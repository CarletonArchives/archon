/****************************************
    Barebones Lightbox Template
    by Kyle Schaeffer
    kyleschaeffer.com
    * requires jQuery
****************************************/
//set up basic variables
var curindex; //for changing image
var imageArray; //for changing image
var open = false; //making sure keyoard actions don occur when lightbox is closed
//start a keyboard listener
window.addEventListener("keydown", checkKeyPressed, false);
// display the lightbox
function lightbox(index, imgarray){
    var index = index;
    var imgarray = imgarray;
    var imgname = imgarray[index][0];
    var imgurl = imgarray[index][1];
    curindex = index;
    imageArray = imgarray;
    // add lightbox/shadow <div/>'s if not previously added
    if($('#lightbox').size() == 0){
        var theLightbox = $('<div id="lightbox"></div>');
        var theShadow = $('<div id="lightbox-shadow"></div>');
        $(theShadow).click(function(e){
            closeLightbox();
        });
        $('body').prepend(theShadow);
        $('body').prepend(theLightbox);
    }
    // remove any previously added content
    $('#lightbox').empty();
    var imgheight = ($('#lightbox').height() - 85) + 'px';
    
    // insert image display
    $('#lightbox').append("<div class= imgcontainer><img class ='lb' src='"+imgurl+"' alt = '"+imgname+"' title = \n\
'press escape or tab to exit slide show, and use left and right arrows to change images.' style = 'height:" + imgheight + "; width: auto;' onclick='javascript:nextImage();'></div>");
    $('#lightbox').append("<div style= min-height: 75px><p id='imgtitle'>"+imgname+"</p>");
    $('#lightbox').append("<a href = 'javascript:prevImage();' class = alignleft>Back</a>");
    $('#lightbox').append("<a href = javascript:closeLightbox(); class = aligncenter>Close</a>");   
    $('#lightbox').append("<a href = 'javascript:nextImage();' class = alignright>Next</a></div>");
    var h = document.querySelector('img').naturalWidth;
    
    // move the lightbox to the current window top + 100px
    $('#lightbox').css('top', 10 + '%');
    //try to center the image (still working on this)
    $('#lightbox').css('marginLeft', '-' + $('#lightbox').width() / 2 + 'px');
    
    // display the lightbox
    $('#lightbox-shadow').fadeIn('fast', function(){
    $('#lightbox').fadeIn('fast');
    });
    open = true;
}


// close the lightbox
function closeLightbox(){

    // hide lightbox and shadow <div/>'s
    $('#lightbox').hide();
    $('#lightbox-shadow').fadeOut('slow');

    // remove contents of lightbox in case a video or other content is actively playing
    $('#lightbox').empty();
    open = false;
}
//display the next image in the slide show
function nextImage() {
   curindex += 1;
   //check if on last image
   if (curindex >= imageArray.length){
       curindex = 0;
   }
   $('#lightbox').hide();
   lightbox(curindex, imageArray);
}
//display the previous image in the slideshow
function prevImage() {
    //check if on first image
   curindex -= 1;
   if (curindex < 0){
       curindex = imageArray.length + curindex;
   }
   $('#lightbox').fadeOut('fast');
   $('#lightbox').hide();
   lightbox(curindex, imageArray);
}

//check for various key presses 
function checkKeyPressed(e) {
    if (open){
        switch(e.keyCode) {
        case 27: //esc key to exit
            closeLightbox();
            break;
        case 37: // left arrow for previous image
            prevImage();
            break;
        case 39: //right arrow for next image
            nextImage();
            break;
        case 9: //tab to close
            closeLightbox();
            break;
        
        }
    }
    
}



