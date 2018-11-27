(function(){
  document.addEventListener('DOMContentLoaded', function() {
    var menu = document.querySelector('#actions .btn-group'), eb_Container = document.createElement('div'), obj;
    eb_Container.className = 'btn-group2 dropdown eb_dropdown';
    var langCurr = '', langList = '', langHtml = '';
    for (var k in eb_langs) {
        obj = eb_langs[k];
        if (k == '0') {
            langCurr += '<label for="eb_checkbox" class="btn btn-secondary">' + obj['text'] + '</label><input type="checkbox" id="eb_checkbox">';
        } else {
            langList += '<span class="btn btn-block" onclick="location.href = \'' + obj['url'] + '\';"><span>' + obj['text'] + '</span></span>';
        }
    }
    langHtml = langCurr + (langList != '' ? '<div class="dropdown-menu eb_show">' + langList + '</div>' : '');
    eb_Container.innerHTML = langHtml;
    menu.appendChild(eb_Container);
    document.getElementById("eb_relations_tv").parentNode.parentNode.parentNode.style.display = 'none';
  })
}());