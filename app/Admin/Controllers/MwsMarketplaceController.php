<?php

namespace App\Admin\Controllers;

use App\Models\MwsMarketplace;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class MwsMarketplaceController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(MwsMarketplace::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->SellerId('SellerId');
            $grid->MarketplaceId('MarketplaceId');
            $grid->DefaultCountryCode('DefaultCountryCode');
            $grid->DomainName('DomainName');
            $grid->Name('Name');
            $grid->DefaultCurrencyCode('DefaultCurrencyCode');
            $grid->DefaultLanguageCode('DefaultLanguageCode');
            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(MwsMarketplace::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('SellerId', 'SellerId');
            $form->text('MarketplaceId', 'MarketplaceId');
            $form->text('DefaultCountryCode', 'DefaultCountryCode');
            $form->text('DomainName', 'DomainName');
            $form->text('Name', 'Name');
            $form->text('DefaultCurrencyCode', 'DefaultCurrencyCode');
            $form->text('DefaultLanguageCode', 'DefaultLanguageCode');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
