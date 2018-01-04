jQuery(document).ready(function($){
    $('#eB_relations').css({
            'position' : 'fixed',
            'right' : '0px',
            'bottom':'0',
            'left':'0px',
            'background' : '#39515D',
            'padding':'3px 25px',
            'z-index':'100',
            'color':'#e5eef5'
    });
    $('#eB_relations a').css({
            'display':'inline-block',
            'height':'auto',
            'line-height':'normal',
            'padding':'6px',
            'box-sizing':'border-box',
            'margin':'0 5px',
            'border-radius' : '2px',
            'background':'#337ab7',
            'color':'#fff'
    });
    $('#eB_relations img').css({
            'margin-right':'6px'
    });
    $('.exists').css({
            'background':'#32AB9A',
            'color':'#fff',
            'text-decoration':'none'
    });
    $('.eb_error').css({
            'color':'red',
            'text-decoration':'none'
      });
    $('.create').css({
           'color':'#e5eef5'
    });
    $('#eB_relations h3').css({
            'margin':'0',
            'text-align':'center',
            'font-size':'14px',
            'font-weight':'bold',
            'display':'inline-block',
            'color':'#ffffff',
            'margin-right':'7px'
    });
    $("#eB_relations").parents("tr").css('display', 'none');
    $("#eB_relations").parents("tr").next("tr").css('display', 'none');
    $("#eB_relations").appendTo("#tabGeneral");
})
