// JavaScript Document

$(function(){
	$("#title").toggle(function(){
	if(!$(this).next().is(":animated")){
		$(this).next().animate({height:"hide"},1100);
	}},function(){
	if(!$(this).next().is(":animated")){
		$(this).next().animate({height:"show"},1900);
	}});
});

//sites的script
$("#site1").hover(function(){
	if(!$("#site1").is(":animated")){
$(this).animate({opacity: "1"}, 300).css();
}},function(){
$(this).animate({opacity: "0.7"}, 400).css();
})

$("#site2").hover(function(){
	if(!$("#sites2").is(":animated")){
$(this).animate({opacity: "1" ,}, 200);
}},function(){
$(this).animate({opacity: "0.7"}, 400);
})

$("#site3").hover(function(){
	if(!$(this).is(":animated")){
$(this).animate({opacity: "1",}, 200).css();
}},function(){
	if(!$(this).is(":animated")){
$(this).animate({opacity: "0.7"}, 400).css();
}})

$("#site4").hover(function(){
	if(!$("#home_menu2").is(":animated")){
$(this).animate({opacity: "1"}, 200);
}},function(){
	if(!$("#home_menu2").is(":animated")){
$(this).animate({opacity: "0.7"}, 400);
}})


$("#site5").hover(function(){
	if(!$("#site5").is(":animated")){
$(this).animate({opacity: "1"}, 200).css();
}},function(){
	if(!$("#home_menu3").is(":animated")){
$(this).animate({opacity: "0.7"}, 400).css();
}})

$("#site6").hover(function(){
	if(!$("#site5").is(":animated")){
$(this).animate({opacity: "1"}, 300).css();
}},function(){
	if(!$("#home_menu3").is(":animated")){
$(this).animate({opacity: "0.7"}, 400).css();
}})

$("#site7").hover(function(){
	if(!$("#site5").is(":animated")){
$(this).animate({opacity: "1"}, 300).css();
}},function(){
	if(!$("#home_menu3").is(":animated")){
$(this).animate({opacity: "0.7"}, 400).css();
}})

$("#site8").hover(function(){
	if(!$("#site5").is(":animated")){
$(this).animate({opacity: "1"}, 300).css();
}},function(){
	if(!$("#home_menu3").is(":animated")){
$(this).animate({opacity: "0.7"}, 400).css();
}})
//box3的script
$(".box3").hover(function(){
$(this).animate({bottom: "-60px",opacity: "1"}, 300);
},function(){
$(this).animate({bottom: "-80px",opacity: "0.5"}, 400);
})


//box4的script
$(".box4").hover(function(){
$(this).animate({right: "0",opacity: "0.5"}, 300);
},function(){
$(this).animate({right: "-580px",opacity: "1"}, 400);
})

$(".click,.box5").hover(function(){
$(".box4").animate({right: "0",opacity: "0.5"}, 300).css("background","#C50");
},function(){
$(".box4").animate({right: "-580px",opacity: "1"}, 400).css("background","#69F");
})

	$("p").animate({width: "0"},3003).css("background","#cc66ff");
	$("p").animate({width: "140px"},2200).css("background","#cc66ff");
	$("p").animate({width: "0"},3003).css("background","#cc66ff");
	$("p").animate({width: "140px"},2200).css("background","#cc66ff");
	$("p").hide(3003).css("background","#cc66ff");
	$("p").show(2200).css("background","#cc66ff");
	$("p").hide(3003).css("background","#cc66ff");
	$("p").show(2200).css("background","#cc66ff");
	$(".3").hide(3003);
	$(".3").show(2200);
	$(".3").hide(3003);
	$(".3").show(2200);

		