(function ($) {
	var config = $.extend({
        month_olypic:[31,29,31,30,31,30,31,31,30,31,30,31],//闰年每个月份的天数
        month_normal:[31,28,31,30,31,30,31,31,30,31,30,31],
        month_name:["一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"],
       
    })
    
    //实现onclick向前或向后移动
    $("#pre").on('click',function(e){alert();
        e.preventDefault();
        my_month--;
        if(my_month < 0){
            my_year--;
            my_month = 11; //即12月份
        }
        $.init.refreshDate();
    });

    //获取当天的年月日
    var my_date = new Date();
    var my_year = my_date.getFullYear();//获取年份
    var my_month= my_date.getMonth(); //获取月份，一月份的下标为0
    var my_day  = my_date.getDate();//获取当前日期

    //根据年月获取当月第一天是周几
    var dayStart = function (month,year){
            var tmpDate = new Date(year, month, 1);
            return (tmpDate.getDay());
        }
    //根据年份判断某月有多少天(11,2018),表示2018年12月
    var daysMonth = function (month, year){
        var tmp1 = year % 4;
        var tmp2 = year % 100;
        var tmp3 = year % 400;

        if((tmp1 == 0 && tmp2 != 0) || (tmp3 == 0)){
            return (config.month_olypic[month]);//闰年
        }else{
            return (config.month_normal[month]);//非闰年
        }
    }

    //获取前一天日期
    var getNextDate = function (date, day) { 
		var dd = new Date(date);
		dd.setDate(dd.getDate() + day);
		var y = dd.getFullYear();
        var m = dd.getMonth() + 1 < 10 ? "0" + (dd.getMonth() + 1) : dd.getMonth() + 1;
　　    var d = dd.getDate() < 10 ? "0" + dd.getDate() : dd.getDate();
		return y + "-" + m + "-" + d;
	};

	$.calendar = $.fn.extend({
        _: function (options){
            a =$.extend({
                dayClick:function(date,obj){},
                centel:function(){},
                ok:function(){},
                update:function(){},
                del:function(){},
                url:'',
                data:'',
            },options)

            //实现onclick向前或向后移动
            $("#pre").on('click',function(e){
                e.preventDefault();
                my_month--;
                if(my_month < 0){
                    my_year--;
                    my_month = 11; //即12月份
                }
                $(".date-panel").hide();
                $.calendar.refreshDate();
            });
            $("#next").on('click',function(e){
                e.preventDefault();
                my_month++;
                if(my_month > 11){
                    my_month = 0;
                    my_year++;
                }
                $(".date-panel").hide();
                $.calendar.refreshDate();
            });

            //删除操作
            $(".panel-btn").on('click','.delete-btn',function(){
                a.del();
            });

            //取消操作
            $(".panel-btn").on('click','.centel-btn',function(){
                a.centel();
            });
            //提交操作
            $(".panel-btn").on('click','.ok-btn',function(){
                a.ok();
            });
            //更新操作
            $(".panel-btn").on('click','.update-btn',function(){
                var date = $(this).attr('data-date');
                a.update();
            });

            if( a.url!=''){
                $.ajax({
                    url: a.url,
                    type: "GET",
                    async: false,
                    success: function(data) {
                        resp = data;
                        resp = $.parseJSON(resp);
                        console.log(resp);
                        a.data = resp.data;
                    }
                })
            }
            $.calendar.refreshDate();
        },
        refreshDate:function(options){
            c =$.extend({
                str:'',
                url:'',
                data:a.data,
            },options)

            //计算当月的天数和每月第一天都是周几，day_month和day_year都从上面获得
            var totalDay = daysMonth(my_month,my_year);
            var firstDay = dayStart(my_month, my_year);

            var day_content     = '';   //日期中的价格
            var exist_content   = '';   //如果有内容时的标记class
            //添加每个月的空白部分
            for(var i = 0; firstDay > i; firstDay--){
                var day = my_year+'-'+(parseInt(my_month)+1)+'-1';
                day = getNextDate(day,-firstDay)
                var d=day.split("-");
                if(typeof c.data == 'object' && c.data && c.data[day]){
                    day_content = $.calendar.make_day_content(c.data,day);
                    exist_content = ' exist_content';
                    //console.log(day_content)
                } 
                c.str += "<li class='lightgrey days_li "+exist_content+"' id='date-"+day+"' data-date='"+day+"'><div class='fc-day-number'>"+d[2]+"</div>"+day_content+"</li>";
            }
 
            //从一号开始添加知道totalDay，并为pre，next和当天添加样式
            var myclass;
            for(var i = 1; i <= totalDay; i++){
                //三种情况年份小，年分相等月份小，年月相等，天数小
                //点击pre和next之后，my_month和my_year会发生变化，将其与现在的直接获取的再进行比较
                //i与my_day进行比较,pre和next变化时，my_day是不变的
                //console.log(my_year+" "+my_month+" "+my_day);
                //console.log(my_date.getFullYear()+" "+my_date.getMonth()+" "+my_date.getDay());
                if((my_year < my_date.getFullYear())||(my_year == my_date.getFullYear() && my_month < my_date.getMonth()) || (my_year == my_date.getFullYear() && my_month == my_date.getMonth() && i < my_day)){
                    myclass = "lightgrey days_li";
                }else if(my_year == my_date.getFullYear() && my_month == my_date.getMonth() && i == my_day){
                    myclass = "green greenbox days_li";
                }else{
                    myclass = "darkgrey days_li";
                }
                var _month = (parseInt(my_month) + 1) < 10 ? "0" + (parseInt(my_month) + 1) : parseInt(my_month)+1;
　　             var _day = i < 10 ? "0" + i : i;

                var _today = my_year+'-'+_month+'-'+_day;
                day_content     = '';
                exist_content   = '';
                if(typeof c.data == 'object' && c.data && c.data[_today]){
                    day_content = $.calendar.make_day_content(c.data,_today);
                    exist_content = ' exist_content';
                    //console.log(day_content)
                } 
                c.str += "<li class='"+myclass+exist_content+"' id='date-"+_today+"' data-date='"+_today+"'><div class='fc-day-number'>"+_day+"</div>"+day_content+"</li>";
            }

            $("#days").html(c.str);
            $("#calendar-title").html(config.month_name[my_month]);
            $("#calendar-year").html(my_year);

            $(".calendar #days .days_li").on('click',function(){
                var date = $(this).attr('data-date');
                a.dayClick(date,this);
            });
        },
        make_day_content:function(data,day){
            var _d = '<ul class="day-content">\
                  <li class="day-content-adult">\
                    <div class="day-content-adult-title">成</div>\
                    <div class="day-content-adult-price"><span class="adult-price">'+data[day].adult_price
+' </span><sub>￥</sub></div>\
                  </li>\
                  <li class="day-content-child">\
                    <div class="day-content-child-title">儿</div>\
                    <div class="day-content-child-price"><span class="child-price">'+data[day].child_price+' </span><sub>￥</sub></div>\
                  </li>\
                  <li class="day-content-single">\
                    <div class="day-content-child-title">单</div>\
                    <div class="day-content-child-price"><span class="child-price">'+data[day].single+' </span><sub>￥</sub></div>\
                  </li>\
                  <li class="day-content-stock">\
                    <div class="day-content-child-title">库</div>\
                    <div class="day-content-child-price"><span class="child-price">' +data[day].stock+' </span><sub>位</sub></div>\
                  </li>\
                </ul>';
            return _d;
        }
    });
}(jQuery));