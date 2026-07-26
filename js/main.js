// JavaScript Document
    //sites的script

$(function(){
	$("#biobox").hover(function(){
	if(!$(this).is(":animated")){
		$(this).animate({width:"900px"},800);
		$(".biotext").animate({width:"670px"},800);
	}},function(){
	if(!$(this).is(":animated")){
		$(this).animate({width:"230px"},500);
		$(".biotext").animate({width:"0px"},500);
	}});
});