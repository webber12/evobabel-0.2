(function(){
  document.addEventListener('DOMContentLoaded', function() {
    var menu = document.querySelector('h1'), eb_Container = document.createElement('div'), eb_selector = document.createElement('select'), obj, eb_option;
    
    eb_Container.className = 'btn-group2 dropdown';
    eb_Container.style.maxWidth = '200px';
    menu.style.paddingLeft = '1.5rem';
    
    eb_selector.id='eb_seletor';
    eb_selector.name = 'eb_seletor';
    eb_selector.className = 'form-control';
    eb_selector.style.backgroundColor = '#dfdfdf';
    eb_selector.style.cursor = 'pointer';
    
    for (var k in eb_langs) {
        obj = eb_langs[k];
        eb_option = document.createElement('option');
        eb_option.text = obj['text'];
        eb_option.value = obj['url'];
        if (obj['url'] == '#') {
            eb_option.selected = true;
        }
        eb_selector.appendChild(eb_option);
    }
    
    eb_Container.appendChild(eb_selector);
    menu.appendChild(eb_Container);
    
    eb_selector.addEventListener("change", function() {
        if (eb_selector.value != '#') {
            location.href = eb_selector.value;
        }
    });

  })
}());