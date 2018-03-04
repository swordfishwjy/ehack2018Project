// JavaScript Document
(function(jQuery){
	jQuery.fn.th_focus_swing = function(options)
	{
		var defaults = {
			time		:3500,		//轮换秒数
            index		:1,			//默认第几张		
			speed		:500,		//切换时间
			dis			:1000,
			splits 		:1			//总标签
		};
		var opts = jQuery.extend(defaults, options);
		
		var _index = opts.index;
		var _time = opts.time;
		var _speed = opts.speed;
		var _dis = opts.dis;
		var _splits = opts.splits;
		
		var _this = jQuery(this);
		
		var node_ul = _this.find(".contentimg");	
		var node_li = node_ul.find("li");
		var node_li_desc = jQuery(".contentdesc").find("li");
		var node_li_nav = jQuery(".mfoc_nav").find("li");
		
		var li_len = node_li.length;
		
		var _countIndex = (node_li.length/opts.split -  1)    
		var _start_left = node_ul.css("left");                
 		
		var _timer = setInterval(show, _time);

        init();
		//alert(1);
		function init() {
			node_ul.mouseover(function() {
				_timer = clearInterval(_timer);
			}).mouseout(function() {
				_timer = setInterval(show, _time);
			});
			node_li_desc.mouseover(function() {
				_timer = clearInterval(_timer);
			}).mouseout(function() {
				_timer = setInterval(show, _time);
			});
			
			node_li_nav.mouseover(function() {
				 node_ul.stop(true, true);
				 node_li_desc.stop(true, true);
				 node_li_desc.eq(_index-1).css("display", "none");
				 node_li_nav.eq(_index-1).removeClass("selected");
				 _index = parseInt(jQuery(this).attr("_index"));
				 node_li_desc.eq(_index-1).fadeIn(_speed);
				 node_li_nav.eq(_index-1).addClass("selected");
				 _left = -_dis*(_index - 1); 
				 node_ul.animate({"left": _left}, _speed);
				_timer = clearInterval(_timer);
			}).mouseout(function() {
				_timer = setInterval(show, _time);
			});
		}
		
		function show() {
                        //alert(2);
			node_ul.stop(true, true);
			node_li_nav.eq(_index-1).removeClass("selected");
			node_li_desc.eq(_index-1).css("display", "none");
			_index++;
			if(_index > li_len) {
				node_ul.append(node_ul.find("li:lt(1)"));
				node_ul.css("left", parseInt(node_ul.css("left")) + _dis);
				node_li_nav.eq(0).addClass("selected");
				node_li_desc.eq(0).fadeIn(_speed);
			}
			else {
				node_li_nav.eq(_index-1).addClass("selected");
				node_li_desc.eq(_index-1).fadeIn(_speed);
			}
			var _left = parseInt(node_ul.css("left")) - _dis;
			node_ul.animate({"left": _left}, _speed, function() {
					if(_index > li_len) {
						node_ul.prepend(node_ul.find("li:gt("+(li_len-_splits-1)+")"));
						node_ul.css("left", 0);
						_index = 1;
					}
					
			});
			
		}
	}
})(jQuery);

$(document).ready(function(){
	//focus
	$(".focusbox").th_focus_swing();
})