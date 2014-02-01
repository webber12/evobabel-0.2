window.addEvent("domready", function() {
    var eB=document.getElementById("eB_relations");
    $$('#eB_relations').set({
        styles:{
            'position' : 'absolute',
            'top' : '1px',
            'right' : '1px',
            'background' : '#f1f1f1',
            'width':'172px',
            'border':'solid 1px #dddddd',
            'padding':'1px'
        }
    });
    $$('#eB_relations a').set({
        styles:{
            'width':'158px',
            'display':'block',
            'height':'20px',
            'line-height':'20px'
        }
    });
    $$('#eB_relations img').set({
        styles:{
            'margin-right':'6px'
        }
    });
    $$('.eB_current').set({
        styles:{
            'width':'158px',
            'display':'block',
            'height':'20px',
            'line-height':'20px',
            'background':'#fafafa',
            'padding':'7px 8px 7px 4px',
            'border-top':'solid 1px #dddddd',
            'border-bottom':'solid 1px #dddddd'
        }
    });
    $$('#eB_relations h3').set({
        styles:{
            'height':'35px',
            'line-height':'35px',
            'margin':'0',
            'text-align':'center',
            'font-size':'14px',
            'font-weight':'bold'
        }
    });
    $j("#eB_relations").parent().parent().hide();
    $j("#tabGeneral").prepend($j("#eB_relations"));
})

