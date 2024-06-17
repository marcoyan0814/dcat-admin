<?php

namespace Dcat\Admin\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Auth\Permission;
use Dcat\Admin\Http\Repositories\Role;
use Dcat\Admin\Show;
use Dcat\Admin\Support\Helper;
use Dcat\Admin\Widgets\Tree;

class RoleController extends AdminController
{
    public function title()
    {
        return trans('admin.roles');
    }

    protected function grid()
    {
        return new Grid(new Role(), function (Grid $grid) {
            $grid->disableViewButton();
            $grid->disableRowSelector();
            $grid->disableBatchDelete();

            if (Admin::user()->isRole('administrator') == false) {
                $grid->model()->where('id', '!=', 1);
            }

            //$grid->column('id', 'ID')->sortable();
            $grid->column('slug')->label('primary');
            $grid->column('name');

            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->disableEditButton();
            $grid->showQuickEditButton();
            $grid->quickSearch(['id', 'name', 'slug']);
            $grid->enableDialogCreate();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $roleModel = config('admin.database.roles_model');
                if ($roleModel::isAdministrator($actions->row->slug)) {
                    $actions->disableDelete();
                }

                if (Admin::user()->isRole('administrator') !== true && $actions->row->slug == "webadmin") {
                    $actions->disableEdit();
                    $actions->disableQuickEdit();
                    $actions->disableDelete();
                }
            });
        });
    }

    protected function detail($id)
    {
        //非開發者不顯示
        if (Admin::user()->isRole('administrator') == false && in_array($id,[1,2])) {
            Permission::error();
        }

        return Show::make($id, new Role('permissions'), function (Show $show) {
            //$show->field('id');
            $show->field('slug');
            $show->field('name');

            $show->field('permissions')->unescape()->as(function ($permission) {
                $permissionModel = config('admin.database.permissions_model');
                $permissionModel = new $permissionModel();
                $nodes = $permissionModel->allNodes();

                $tree = Tree::make($nodes);

                $keyName = $permissionModel->getKeyName();
                $tree->check(
                    array_column(Helper::array($permission), $keyName)
                );

                return $tree->render();
            });

            $show->field('created_at');
            $show->field('updated_at');

            $roleModel = config('admin.database.roles_model');
            if ($show->getKey() == $roleModel::ADMINISTRATOR_ID) {
                $show->disableDeleteButton();
            }

        });
    }

    public function form()
    {
        $with = ['permissions'];

        if ($bindMenu = config('admin.menu.role_bind_menu', true)) {
            $with[] = 'menus';
        }

        return Form::make(Role::with($with), function (Form $form) use ($bindMenu) {
            $roleTable = config('admin.database.roles_table');
            $connection = config('admin.database.connection');

            $id = $form->getKey();

            $form->display('id', 'ID');

            $form->text('slug', trans('admin.slug'))
                ->required()
                ->creationRules(['required', "unique:{$connection}.{$roleTable}"])
                ->updateRules(['required', "unique:{$connection}.{$roleTable},slug,$id"]);

            $form->text('name', trans('admin.name'))->required();

            $form->tree('permissions')
                ->nodes(function () {
                    $permissionModel = config('admin.database.permissions_model');
                    $permissionModel = new $permissionModel();

                    //非開發者
                    if (Admin::user()->isRole('administrator') == false) {
                        return $permissionModel->allNodes()->reject(function ($value, $key) {
                            return $value->id==2 || $value->parent_id==2;
                        });
                    }
                    return $permissionModel->allNodes();
                })
                ->customFormat(function ($v) {
                    if (! $v) {
                        return [];
                    }

                    return array_column($v, 'id');
                });

            if ($bindMenu) {
                $form->tree('menus', trans('admin.menu'))
                    ->treeState(false)
                    ->setTitleColumn('title')
                    ->nodes(function () {
                        $model = config('admin.database.menu_model');
                        //非開發者
                        if (Admin::user()->isRole('administrator') == false) {
                            return (new $model())->allNodes()->reject(function ($value, $key) {
                                return $value->id==3 || $value->parent_id==3;
                            });
                        }
                        return (new $model())->allNodes();
                    })
                    ->customFormat(function ($v) {
                        if (! $v) {
                            return [];
                        }

                        return array_column($v, 'id');
                    });
            }

            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

            $roleModel = config('admin.database.roles_model');
            if ($id == $roleModel::ADMINISTRATOR_ID) {
                $form->disableDeleteButton();
            }
        })->saved(function () {
            $model = config('admin.database.menu_model');
            (new $model())->flushCache();
        })->deleting(function () {
            if (Admin::user()->isRole('administrator') == false) {
                $roleModel = config('admin.database.roles_model');
                if (in_array($roleModel::ADMINISTRATOR_ID, Helper::array(request()->route('role')))
                    || in_array($roleModel::WEBADMIN_ID, Helper::array(request()->route('role')))) {
                    Permission::error();
                }
            }
        });
    }

    public function destroy($id)
    {
        $roleModel = config('admin.database.roles_model');
        if (in_array($roleModel::ADMINISTRATOR_ID, Helper::array($id))) {
            Permission::error();
        }

        return parent::destroy($id);
    }
}
