window.addEvent("domready", function() {
    $('eB_relations').set({
        styles : {
            'position' : 'fixed',
            'right' : '15px',
            'bottom':'0',
            'left':'15px',
            'background' : '#f1f1f1',
            'border-top':'solid 1px #dddddd',
            'border-bottom':'solid 1px #dddddd',
            'padding':'2px 7px',
            'z-index':'10'
        }
    });
    $$('#eB_relations a').set({
        styles : {
            'display':'inline-block',
            'height':'auto',
            'line-height':'normal',
            'padding':'6px',
            'box-sizing':'border-box',
            'margin':'0 5px'
        }
    });
    $$('#eB_relations img').set({
        styles : {
            'margin-right':'6px'
        }
    });
    $$('.exists').set({
        styles : {
            'background':'#32AB9A',
            'color':'#fff',
            'text-decoration':'none'
        }
    });
    $$('.eb_error').set({
        styles : {
            'color':'red',
            'text-decoration':'none'
        }
      });
    $$('.create').set({
        styles : {
           'color':'#888'
        }
    });
    $$('#eB_relations h3').set({
        styles : {
            'margin':'0',
            'text-align':'center',
            'font-size':'14px',
            'font-weight':'bold',
            'display':'inline-block',
            'color':'#000',
            'margin-right':'7px'
        }
    });
    $("eB_relations").getParent().getParent().setStyle('display', 'none');
    $("tabGeneral").insertBefore($("eB_relations"), $("tabGeneral").firstChild);
})
