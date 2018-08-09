<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Library\MWSCore\Sellers\MWSListMarketplaceParticipation;
use App\Models\MwsConfig;
use App\Models\MwsMarketplace;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\MessageBag;

class MwsConfigController extends Controller
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
        return Admin::grid(MwsConfig::class, function (Grid $grid) {

            $grid->id('ID')->sortable();
            $grid->SellerId();
            $grid->MWSAWSAccessKeyId();
            $grid->MWSSecretKey();
            $grid->ServiceUrl();
            $grid->Status();
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
        return Admin::form(MwsConfig::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('SellerId', 'SellerId')->rules('required')->help('卖家Id');
            $form->text('MWSAWSAccessKeyId', 'MWSAWSAccessKeyId')->rules('required');
            $form->text('MWSSecretKey', 'MWSSecretKey')->rules('required');
            $form->select('ServiceUrl', 'ServiceUrl')->options([
                'https://mws.amazonservices.com/' => 'North America (NA)|Brazil (BR)',
                'https://mws-eu.amazonservices.com/' => 'Europe (EU)',
                'https://mws.amazonservices.in/' => 'India (IN)',
                'https://mws.amazonservices.com.cn/' => 'China (CN)',
                'https://mws.amazonservices.jp/' => 'Japan (JP)',
            ])->rules('required')->help('选择你程序的站点,例如日本渠道就选择<font color="red">Japan (JP)</font>');
            $form->switch('Status', 'Status')->default(1)->help('是否生效,蓝色生效');
            $form->textarea('Remark', 'Remark');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            $mws = false;
            $form->saving(function ($form) use ($mws) {
                try {
                    $mws = new MWSListMarketplaceParticipation("Test SellerId = " . $form->SellerId, $form->SellerId, $form->MWSAWSAccessKeyId, $form->MWSSecretKey, $form->ServiceUrl, 'ListParticipation');
                    $xml = $mws->ListMarketplaceParticipations();
                    if (!$xml) {
                        $error = new MessageBag([
                            'title' => 'Validator Fail',
                            'message' => 'The Seller is not allow to create!',
                        ]);
                        return back()->with(compact('error'));
                    };
                    //验证当前输入信息是否有误
                    if ($mws) {
                        $list = $mws->getMarketplaceList();
                        foreach ($list as $item) {
                            $marketPlace = new MwsMarketplace();
                            $marketPlace->SellerId = $form->SellerId;
                            $marketPlace->MarketplaceId = $item['MarketplaceId'];
                            $marketPlace->Name = $item['Name'];
                            $marketPlace->DefaultCountryCode = $item['DefaultCountryCode'];
                            $marketPlace->DomainName = $item['DomainName'];
                            $marketPlace->DefaultCurrencyCode = $item['DefaultCurrencyCode'];
                            $marketPlace->DefaultLanguageCode = $item['DefaultLanguageCode'];
                            $marketPlace->save();
                        }
                    }
                } catch (\Exception $e) {
                    $error = new MessageBag([
                        'title' => 'Validator Fail',
                        'message' => 'The Seller is not allow to create!'.$e->getMessage(),
                    ]);
                    return back()->with(compact('error'));
                }});

        });
    }
}
