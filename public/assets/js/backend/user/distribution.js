define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    return {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/distribution/index' + location.search,
                    table    : 'user_distribution',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url     : $.fn.bootstrapTable.defaults.extend.index_url,
                pk      : 'id',
                sortName: 'id',
                columns : [
                    [
                        {field: 'superior.id', title: __('Superior_user_id'), sortable: true, visible: false},
                        {
                            field    : 'superior.avatar',
                            title    : __('Superior_user_avatar'),
                            operate  : false,
                            events   : Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {field: 'superior.nickname', title: __('Superior_user'), operate: 'LIKE'},
                        {field: 'superior.mobile', title: __('Superior_user_mobile'), operate: 'LIKE', visible: false},
                        {
                            field     : 'superior.level',
                            title     : __('Superior_user_level'),
                            sortable  : true,
                            searchList: {"1": __('Level 1'), "2": __('Level 2')},
                            formatter : Table.api.formatter.normal
                        },
                        {
                            field     : 'superior.is_valid',
                            title     : __('Superior_user_is_valid'),
                            sortable  : true,
                            searchList: {"1": __('Is_valid 1'), "2": __('Is_valid 2')},
                            formatter : Table.api.formatter.normal
                        },
                        {field: 'junior.id', title: __('Junior_user_id'), sortable: true, visible: false},
                        {
                            field    : 'junior.avatar',
                            title    : __('Junior_user_avatar'),
                            operate  : false,
                            events   : Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {field: 'junior.nickname', title: __('Junior_user'), operate: 'LIKE'},
                        {field: 'junior.mobile', title: __('Junior_user_mobile'), operate: 'LIKE', visible: false},
                        {
                            field     : 'junior.level',
                            title     : __('Junior_user_level'),
                            sortable  : true,
                            searchList: {"1": __('Level 1'), "2": __('Level 2')},
                            formatter : Table.api.formatter.normal
                        },
                        {
                            field     : 'junior.is_valid',
                            title     : __('Junior_user_is_valid'),
                            sortable  : true,
                            searchList: {"1": __('Is_valid 1'), "2": __('Is_valid 2')},
                            formatter : Table.api.formatter.normal
                        },
                        {
                            field     : 'status',
                            title     : __('Status'),
                            sortable  : true,
                            searchList: {"1": __('Status 1'), "2": __('Status 2')},
                            formatter : Table.api.formatter.normal
                        },
                        {field: 'describe', title: __('Describe'), operate: false},
                        {
                            field       : 'create_time',
                            title       : __('Create_time'),
                            sortable    : true,
                            operate     : 'RANGE',
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