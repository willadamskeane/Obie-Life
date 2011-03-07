$.fn.extend({ 
                disableSelection: function() { 
                    this.each(function() { 
                        if (typeof this.onselectstart != 'undefined') {
                            this.onselectstart = function() { return false; };
                        } else if (typeof this.style.MozUserSelect != 'undefined') {
                            this.style.MozUserSelect = 'none';
                        } else {
                            this.onmousedown = function() { return false; };
                        }
                    }); 
                } 
            });
            
function updateEvents()
{
	var categories='';
	$('.category :checkbox').each(function(){
		if ($(this).attr('checked'))
		{
			if (categories!=''){categories+=':';}
			categories+=$(this).attr('id').substring(4);
		}
	});
	var sorting=0;
	if ($('#hot:checked').val()=='on')
	{
		sorting=1;
	}
	$('.events').load('includes/actions.php?getevents','categories='+categories+'&sorting='+sorting);
}

$(document).ready(function(){
	
	$('#tabs').tabs();	
	
	$( "#accordion" ).accordion({autoHeight:false});
	
	$('#newEventButton').button({
        icons: {
            primary: "ui-icon-plusthick"
        }});
	$('#newEventButton').click(function(){
		$('#newEvent').dialog('open');
		return false;
	});
	
	$('#register').button();
        
    $('#newAccountButton').button();
    
	$("input:checkbox").uniform();
	$('#sorting').buttonset();
	
	$('.category').disableSelection();
	
	$('.category').mouseover(function(){
		$(this).css('border-color','');
	});
	
	
	$('.category').mouseleave(function(){
		$(this).css('border-color','transparent');
	});
	
	$('.category input:checkbox').change(function(){
		if ($(this).parents('.category').css('font-weight')!='bold'){
			$(this).parents('.category').css('font-weight','bold');
		}else{
			$(this).parents('.category').css('font-weight','normal');
		}
		if ($(this).attr('id')!='cat_0')
		{
			if ($('.category :checked').size()==0){
				$('#cat_0').attr('checked', true);
				$('#cat_0').parents('.category').css('font-weight','bold');
			}else{
				$('#cat_0').attr('checked', false);
				$('#cat_0').parents('.category').css('font-weight','normal');
			}
			$.uniform.update('.category input:checkbox');
		}else{
			if ($(this).is(':checked'))
			{
				$('.category input:checkbox').attr('checked',false);
				$('.category input:checkbox').parents('.category').css('font-weight','normal');
				$(this).attr('checked',true);
				$(this).parents('.category').css('font-weight','bold');
				$.uniform.update('.category input:checkbox');
			}
		}
		updateEvents();

	});
	
	$('#sorting').change(function(){
		updateEvents();
	});
	
	// --------------------------------------------
	
	
	$('.tab:not(.liked)').live('mouseover',function(){
		$(this).css('color','#ffffff');
		$(this).addClass('tabcat_'+$(this).attr('name'));
		$(this).removeClass('maincat_'+$(this).attr('name'));
	});
	
	$('.tab:not(.liked)').live('mouseleave',function(){
		$(this).css('color','inherit');
		$(this).addClass('maincat_'+$(this).attr('name'));
		$(this).removeClass('tabcat_'+$(this).attr('name'));
	});	
	
	$('.tab').live('click',function(){
		var eID=$(this).parents(".eventInfoContainer").parents(".event").attr("id").substring(6);
		$(this).load("includes/actions.php?likeevent","eid="+eID);
		$(this).css('color','inherit');
		$(this).addClass('tabcat_'+$(this).attr('name'));
		$(this).addClass('liked');
		$(this).css('color','#ffffff');
		$(this).removeClass('maincat_'+$(this).attr('name'));
		return false;
	});	
	
	$('.right').live('mouseover',function(){
	
	});
	
	$(".right").live('click',function() {
		if ($(this).attr('title')=='close'){
		    $(this).attr('title','open');
		    var eID=$(this).parents(".eventInfoContainer").parents(".event").attr("id").substring(6);
		    $(this).animate({ height: $(this).parents(".event").find(".eventDescription").outerHeight(true) + 42}, 200);
		    $(this).parents(".eventInfoContainer").animate({ height: $(this).parents(".event").find(".eventDescription").outerHeight(true) + 54 }, 200);
		    $(this).parents(".event").children(".comments").slideToggle();
		    $(this).parents(".event").children(".comments").children(".commentsContainer").load('includes/actions.php?getcomments','eid='+eID);
		    $(this).parents(".event").find(".eventDescription").fadeIn('fast');
		    $(this).parents(".event").find(".postComment").button();
		}else{
		    $(this).attr('title','close');
		    $(this).animate({ height: '28px' }, 200);
		    $(this).parents(".eventInfoContainer").animate({ height: "40px" }, 200);
		    $(this).parents(".event").find(".eventDescription").fadeOut('fast');
		    $(this).parents(".eventInfoContainer").parents(".event").children(".comments").slideToggle();
		}
		return false;
	});
	
	$(".commentBox").live('click',function(){
    	$(this).attr("rows","4");
    	$(this).parents(".commentBoxContainerUnselected").addClass("commentBoxContainerSelected");
    	$(this).parents(".commentBoxContainerUnselected").children(".postComment").show();
    });
    
    $(".timecat").live('click',function(){
    	$("#"+$(this).attr("id")+"_div").slideToggle();
    	return false;
    });
    
    $(".postComment").live('click',function(){
    	$(this).button( "option", "disabled", true );
    	var eID=$(this).parents(".event").attr("id").substring(6);
    	var text=$(this).parents(".commentBoxContainerSelected").children(".commentBox").val();
    	$.post("includes/actions.php?postcomment&eid="+eID,"text="+text,function(data){
	    	$("#event_"+data.eid).find(".commentBox").val('');
	    	$("#event_"+data.eid).find(".postComment").button( "option", "disabled", false );
	    	$("#event_"+data.eid).find(".commentsContainer").html(data.html);
    		},"json");
    });
	
});
