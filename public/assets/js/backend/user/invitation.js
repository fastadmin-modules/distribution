define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    return {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/invitation/index' + location.search,
                    table    : 'user_invitation',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url     : $.fn.bootstrapTable.defaults.extend.index_url,
                pk      : 'id',
                sortName: 'user_id',
                columns : [
                    [
                        {field: 'user_id', title: __('User_id'), sortable: true},
                        {
                            field    : 'user.avatar',
                            title    : __('User.avatar'),
                            operate  : 'LIKE',
                            events   : Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
                        {field: 'user.mobile', title: __('User.mobile'), operate: 'LIKE'},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {
                            field    : 'qr_code',
                            title    : __('Qr_code'),
                            operate  : false,
                            events   : Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {
                            field       : 'create_time',
                            title       : __('Create_time'),
                            operate     : 'RANGE',
                            sortable    : true,
                            addclass    : 'datetimerange',
                            autocomplete: false,
                            formatter   : Table.api.formatter.datetime
                        },
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api  : {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
});