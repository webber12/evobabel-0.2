<?php
//@author webber (web-ber12@yandex.ru)

if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}

//активный язык отдельно от списка
$activeLang='<div id="curr_lang"><img src="assets/images/langs/flag_[+alias+].jpg"> <a href="javascript:;">[+name+]</a> <img src="site/imgs/lang_pict.jpg" alt="" id="switcher"></div>'; 

//активный язык в списке
$activeRow='<div class="active"><img src="assets/images/langs/flag_[+alias+].jpg"> &nbsp;<a href="[+url+]">[+name+]</a></div>';

//неактивный язык списка
$unactiveRow='<div><img src="assets/images/langs/flag_[+alias+].jpg"> &nbsp;<a href="[+url+]">[+name+]</a></div>';

//обертка списка языков
$langOuter='<div class="other_langs">[+wrapper+]</div>';



?>