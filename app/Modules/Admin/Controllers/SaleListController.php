<?php

namespace App\Modules\Admin\Controllers;

/**
 * 销售明细列表
 * Class SaleListController
 * @package App\Modules\Admin\Controllers
 */
class SaleListController extends BaseController
{
    public function actionIndex()
    {
        load_helper('order');
        load_lang('statistic', 'admin');

        $this->smarty->assign('lang', $GLOBALS['_LANG']);

        if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'query' || $_REQUEST['act'] == 'download')) {
            // 检查权限
            check_authz_json('sale_order_stats');
            if (strstr($_REQUEST['start_date'], '-') === false) {
                $_REQUEST['start_date'] = local_date('Y-m-d', $_REQUEST['start_date']);
                $_REQUEST['end_date'] = local_date('Y-m-d', $_REQUEST['end_date']);
            }
            /*------------------------------------------------------ */
            //--Excel文件下载
            /*------------------------------------------------------ */
            if ($_REQUEST['act'] == 'download') {
                $file_name = $_REQUEST['start_date'] . '_' . $_REQUEST['end_date'] . '_sale';
                $goods_sales_list = $this->get_sale_list(false);
                header("Content-type: application/vnd.ms-excel; charset=utf-8");
                header("Content-Disposition: attachment; filename=$file_name.xls");

                // 文件标题
                echo ecs_iconv(CHARSET, 'GB2312', $_REQUEST['start_date'] . $GLOBALS['_LANG']['to'] . $_REQUEST['end_date'] . $GLOBALS['_LANG']['sales_list']) . "\t\n";

                // 商品名称,订单号,商品数量,销售价格,销售日期
                echo ecs_iconv(CHARSET, 'GB2312', $GLOBALS['_LANG']['goods_name']) . "\t";
                echo ecs_iconv(CHARSET, 'GB2312', $GLOBALS['_LANG']['order_sn']) . "\t";
                echo ecs_iconv(CHARSET, 'GB2312', $GLOBALS['_LANG']['amount']) . "\t";
                echo ecs_iconv(CHARSET, 'GB2312', $GLOBALS['_LANG']['sell_price']) . "\t";
                echo ecs_iconv(CHARSET, 'GB2312', $GLOBALS['_LANG']['sell_date']) . "\t\n";

                foreach ($goods_sales_list['sale_list_data'] as $key => $value) {
                    echo ecs_iconv(CHARSET, 'GB2312', $value['goods_name']) . "\t";
                    echo ecs_iconv(CHARSET, 'GB2312', '[ ' . $value['order_sn'] . ' ]') . "\t";
                    echo ecs_iconv(CHARSET, 'GB2312', $value['goods_num']) . "\t";
                    echo ecs_iconv(CHARSET, 'GB2312', $value['sales_price']) . "\t";
                    echo ecs_iconv(CHARSET, 'GB2312', $value['sales_time']) . "\t";
                    echo "\n";
                }
                exit;
            }
            $sale_list_data = $this->get_sale_list();
            $this->smarty->assign('goods_sales_list', $sale_list_data['sale_list_data']);
            $this->smarty->assign('filter', $sale_list_data['filter']);
            $this->smarty->assign('record_count', $sale_list_data['record_count']);
            $this->smarty->assign('page_count', $sale_list_data['page_count']);

            return make_json_result($this->smarty->fetch('sale_list.htm'), '', ['filter' => $sale_list_data['filter'], 'page_count' => $sale_list_data['page_count']]);
        }

        /**
         *商品明细列表
         */
        else {
            // 权限判断
            admin_priv('sale_order_stats');
            // 时间参数
            if (!isset($_REQUEST['start_date'])) {
                $start_date = local_strtotime('-7 days');
            }
            if (!isset($_REQUEST['end_date'])) {
                $end_date = local_strtotime('today');
            }

            $sale_list_data = $this->get_sale_list();
            // 赋值到模板
            $this->smarty->assign('filter', $sale_list_data['filter']);
            $this->smarty->assign('record_count', $sale_list_data['record_count']);
            $this->smarty->assign('page_count', $sale_list_data['page_count']);
            $this->smarty->assign('goods_sales_list', $sale_list_data['sale_list_data']);
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['sell_stats']);
            $this->smarty->assign('full_page', 1);
            $this->smarty->assign('start_date', local_date('Y-m-d', $start_date));
            $this->smarty->assign('end_date', local_date('Y-m-d', $end_date));
            $this->smarty->assign('ur_here', $GLOBALS['_LANG']['sale_list']);
            $this->smarty->assign('cfg_lang', $GLOBALS['_CFG']['lang']);
            $this->smarty->assign('action_link', ['text' => $GLOBALS['_LANG']['down_sales'], 'href' => '#download']);

            // 显示页面

            return $this->smarty->display('sale_list.htm');
        }
    }
    /**
     *获取销售明细需要的函数
     */
    /**
     * 取得销售明细数据信息
     * @param   bool $is_pagination 是否分页
     * @return  array   销售明细数据
     */
    private function get_sale_list($is_pagination = true)
    {

        // 时间参数
        $filter['start_date'] = empty($_REQUEST['start_date']) ? local_strtotime('-7 days') : local_strtotime($_REQUEST['start_date']);
        $filter['end_date'] = empty($_REQUEST['end_date']) ? local_strtotime('today') : local_strtotime($_REQUEST['end_date']);

        // 查询数据的条件
        $where = " WHERE og.order_id = oi.order_id" . order_query_sql('finished', 'oi.') .
            " AND oi.add_time >= '" . $filter['start_date'] . "' AND oi.add_time < '" . ($filter['end_date'] + 86400) . "'";

        $sql = "SELECT COUNT(og.goods_id) FROM " .
            $GLOBALS['ecs']->table('order_info') . ' AS oi,' .
            $GLOBALS['ecs']->table('order_goods') . ' AS og ' .
            $where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        // 分页大小
        $filter = page_and_size($filter);

        $sql = 'SELECT og.goods_id, og.goods_sn, og.goods_name, og.goods_number AS goods_num, og.goods_price ' .
            'AS sales_price, oi.add_time AS sales_time, oi.order_id, oi.order_sn ' .
            "FROM " . $GLOBALS['ecs']->table('order_goods') . " AS og, " . $GLOBALS['ecs']->table('order_info') . " AS oi " .
            $where . " ORDER BY sales_time DESC, goods_num DESC";
        if ($is_pagination) {
            $sql .= " LIMIT " . $filter['start'] . ', ' . $filter['page_size'];
        }

        $sale_list_data = $GLOBALS['db']->getAll($sql);

        foreach ($sale_list_data as $key => $item) {
            $sale_list_data[$key]['sales_price'] = price_format($sale_list_data[$key]['sales_price']);
            $sale_list_data[$key]['sales_time'] = local_date($GLOBALS['_CFG']['time_format'], $sale_list_data[$key]['sales_time']);
        }
        $arr = ['sale_list_data' => $sale_list_data, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
        return $arr;
    }
}
