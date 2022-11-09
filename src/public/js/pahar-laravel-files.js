function plfGetCookie(name) {let matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));return matches ? decodeURIComponent(matches[1]) : undefined;}
function plfSetCookie(name, value, options = {}) {options = {path: '/',...options};if (options.expires instanceof Date) { options.expires = options.expires.toUTCString();} let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value); for (let optionKey in options) {updatedCookie += "; " + optionKey;let optionValue = options[optionKey]; if (optionValue !== true) {updatedCookie += "=" + optionValue; } }document.cookie = updatedCookie;}





//jQuery(document).ready(function($){
const plfLoader = '<div class="pre-lds-grid"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>';
const plf = {
    target:false,
    open:  function(){
        let plfPopup = "<div class='plf-bg'></div>";
        plfPopup += "<div class='plf-popup'>"+
            "<div class='plf-popup-outer'>" +
            "<button class='plf-close' type='button'>&#10006;</button>" +
            "<div class='plf-popup-inner'></div>"+
            "</div>"
        "</div>";

        $('body').append(plfPopup);
    },
    close: function (){
        plf.target = false;
        $(document).find('.plf-bg,.plf-popup').remove();
    },
    getData:function(path = ''){
        plfSetCookie('plfLastPath',path);
        $.ajax({
            type:'get',
            url:'/laravel-files?path=' + path,
            beforeSend: function(){
                $(document).find('.plf-popup-inner').html(plfLoader);
            },
            success: function(ans){
                $(document).find('.plf-popup-inner').html(ans);
            }
        })
    }
};



$(document).on('click','.plf-field-body',function(e){
    plf.target = $(e.target).closest('.plf-field-outer');
    let lastPath = plfGetCookie('plfLastPath');
    if(!lastPath){
        lastPath = '';
    }
    plf.open();
    plf.getData(lastPath);
})

$(document).on('click','.plf-close,.plf-bg',function(){
    plf.close();
});

$(document).on('click','.plf-addFolder',function(){
    $(document).find('.plf-new-folder-pop').slideToggle();
})
$(document).on('click','.plf-cancelFolder',function(){
    $(document).find('.plf-new-folder-pop').slideUp();
    $(document).find('.plf-new-folder-form').find('input[name="foldername"]').val('');
})
$(document).on('click','.plf-newFolder',function (){
    let form =  $(document).find('.plf-new-folder-form');
    let url = form.attr('action');
    $.ajax({
        type:'post',
        url:url,
        data:form.serialize(),
        success: function(ans){
            let lastPath = plfGetCookie('plfLastPath');
            if(!lastPath){
                lastPath = '';
            }
            plf.getData(lastPath);
        }
    })
});


$(document).on('click','.plf-search',function(){
    $(document).find('.plf-search-pop').slideToggle();
})
$(document).on('click','.plf-cancelSearch',function(){
    $(document).find('.plf-search-pop').slideUp();
    let lastPath = plfGetCookie('plfLastPath');
    if(!lastPath){
        lastPath = '';
    }
    plf.getData(lastPath);
});

$(document).on('click','.plf-go-search',function (){
    let form =  $(document).find('.plf-search-form');
    let url = form.attr('action');
    let s = form.find('input[name="s"]').val();
    if(s.length<=0){
        let lastPath = plfGetCookie('plfLastPath');
        if(!lastPath){
            lastPath = '';
        }
        plf.getData(lastPath);
        return false;
    }
    $.ajax({
        type:'post',
        url:url,
        data:form.serialize(),
        success: function(ans){
            if(ans.success !== undefined){
                $(document).find('.plf-body .plf-body-inner').html(ans.html);
            }
        }
    })
});


$(document).on('dblclick','.plf-file-item-dir',function(){
    let path = $(this).attr('data-path');
    plf.getData(path);
});





$(document).on('click','.plf-path li.plf-path-li',function(){
    let path = $(this).attr('data-path');
    plf.getData(path);
})


$(document).on('click','.plf-files-form button',function(){
    $(this).siblings('input').trigger('click');
});
$(document).on('change','.plf-files-form input',function(){
    let form = $(this).closest('form');
    let action = form.attr('action');
    let token = form.find('input[name="_token"]').val();
    let folder = form.find('input[name="folder"]').val();
    let files = form.find('input[name="files"]');
    let data = new FormData();
    $.each(files[0].files, function(i, file) {
        data.append('file-'+i, file);
    });
    data.append('folder', folder);
    data.append('_token', token);

    $.ajax({
        type: 'post',
        url: action,
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function (ans){
            let lastPath = plfGetCookie('plfLastPath');
            if(!lastPath){
                lastPath = '';
            }
            plf.getData(lastPath);
        }
    })
});


$(document).on('click','.plf-file-item .plf-pop-remove',function(){
    let confirmText = $(this).attr('data-confirm');
    if(!confirm(confirmText)){
        return false;
    }
    let outer = $(this).closest('.plf-file-item');
    let path = $(this).attr('data-path');
    let action = $(this).attr('data-action');
    $.ajax({
        type:'post',
        url:action,
        data:{path:path},
        success: function (ans){
            if(ans.success !== undefined){
                outer.remove();
            }
        }
    })
});

$(document).on('dblclick','.plf-file-item-file',function(){
    let publicPath = $(this).attr('data-publicPath');
    let name = $(this).find('.plf-filename').text();
    let thumb = $(this).find('.plf-file-img img').attr('src');

    if(plf.target.container !== undefined){
        console.log(plf.target.container);
        plf.target.insertHtml("<img src='/"+ publicPath +"' />");
    }else{
        plf.target.find('.plf-field-body img').attr('src',thumb);
        plf.target.find('.plf-field-name').text(name);
        plf.target.find('input[type="hidden"]').val(publicPath);

        if($(this).find('.plf-file-extension').length){
            plf.target.find('.plf-field-body .plf-field-body-extension').remove();
            let ext = $(this).find('.plf-file-extension').text();
            plf.target.find('.plf-field-body').append("<span class='plf-field-body-extension'>" + ext + "</span>");
        }
    }
    plf.close();
});

$(document).on('click','.plf-field-remove',function(){

    let outer = $(this).closest('.plf-field-outer');
    let placeholder = outer.find('.plf-field-body img').attr('data-placeholder');
    outer.find('.plf-field-body img').attr('src',placeholder);
    outer.find('.plf-field-name').text('');
    outer.find('input[type="hidden"]').val('');
    outer.find('.plf-field-body-extension').remove();
});


//});
