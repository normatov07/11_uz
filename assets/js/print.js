$(function(){
if (window.sidebar || window.opera) { // Mozilla Firefox Bookmark
	window.onload = window.print;
} else if( window.external ) { // IE Favorite
	window.print(); 
}

});