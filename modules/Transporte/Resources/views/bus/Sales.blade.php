@extends('tenant.layouts.app')

@section('content')
    <!--  <tenant-transporte-bus-sales :plantilla="'{{$plantilla}}'"></tenant-transporte-bus-sales>-->

    <div class="draggable-area" style="background:#ddd;width:200px;height:200px;">
		<div id="dragit-contained" style="background: red; width: 50px; height: 50px; cursor: move; position: relative; top: 55px; left: 46px;" class=""></div>
	</div>
    
@endsection

@section('js')
<script>
    $(function() {
        var draggable = $('#dragit-contained'); //element 

            draggable.on('mousedown', function(e){
                var dr = $(this).addClass("drag").css("cursor","move");
                height = dr.outerHeight();
                width = dr.outerWidth();
                max_left = dr.parent().offset().left + dr.parent().width() - dr.width();
                max_top = dr.parent().offset().top + dr.parent().height() - dr.height();
                min_left = dr.parent().offset().left;
                min_top = dr.parent().offset().top;

                ypos = dr.offset().top + height - e.pageY;
                xpos = dr.offset().left + width - e.pageX;
                $(document.body).on('mousemove', function(e){
                    var itop = e.pageY + ypos - height;
                    var ileft = e.pageX + xpos - width;
                    
                    if(dr.hasClass("drag")){
                        if(itop <= min_top ) { itop = min_top; }
                        if(ileft <= min_left ) { ileft = min_left; }
                        if(itop >= max_top ) { itop = max_top; }
                        if(ileft >= max_left ) { ileft = max_left; }
                        dr.offset({ top: itop,left: ileft});
                    }
                }).on('mouseup', function(e){
                        dr.removeClass("drag");
                });
            });
    });
    </script>
@endsection