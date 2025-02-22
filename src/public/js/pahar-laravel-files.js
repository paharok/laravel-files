function plfGetCookie(name) {let matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));return matches ? decodeURIComponent(matches[1]) : undefined;}
function plfSetCookie(name, value, options = {}, callback = null) {options = {path: '/',...options};if (options.expires instanceof Date) { options.expires = options.expires.toUTCString();} let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value); for (let optionKey in options) {updatedCookie += "; " + optionKey;let optionValue = options[optionKey]; if (optionValue !== true) {updatedCookie += "=" + optionValue; } }document.cookie = updatedCookie;if (typeof callback === "function") {callback();} }





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
            url:'/laravel-files?path=' + path + '&d='+Date.now(),
            beforeSend: function(){
                $(document).find('.plf-popup-inner').html(plfLoader);
            },
            success: function(ans){
                $(document).find('.plf-popup-inner').html(ans);
                $(document).trigger('plf-checked');
                showHidePutCancelButton();
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
    token = getPLFToken();
    let data = form.serialize();
    data += "&_token="+token;

    $.ajax({
        type:'post',
        url:url,
        data:data,
        success: function(ans){
            let lastPath = plfGetCookie('plfLastPath');
            if(!lastPath){
                lastPath = '';
            }
            plf.getData(lastPath);
        }
    })
});


$(document).on('keypress','.plf-new-folder-form input[name="foldername"]',function (e) {
    if (e.which == 13) {
        $(this).closest('form').find('.plf-newFolder').trigger('click');
        return false;
    }
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
    let data = form.serialize();
    let token = getPLFToken();
    data += "&_token="+ token;

    $.ajax({
        type:'post',
        url:url,
        data:data,
        success: function(ans){
            if(ans.success !== undefined){
                $(document).find('.plf-body .plf-body-inner').html(ans.html);
                $(document).trigger('plf-checked');
            }
        }
    })
});
$(document).on('keypress', '.plf-search-form input[name="s"]', function (e) {
    if (e.which == 13) {
        $(this).closest('form').find('.plf-go-search').trigger('click');
        return false;
    }
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
    let token = getPLFToken();
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

$(document).on('click','.plf-file-item .plf-pop-rename',function(){
    let promptQuestion = $(this).attr('data-prompt');
    let newName = prompt(promptQuestion, name);

    if(!newName){
        return false;
    }

    let path = $(this).attr('data-path');
    let action = $(this).attr('data-action');
    let token = getPLFToken();

    $.ajax({
        type: "post",
        url: action,
        data: {_token:token,path:path,newName:newName},
        error:function (err1){
            if(err1.responseJSON != undefined && err1.responseJSON.errors != undefined){
                let errors = "";
                for(i in err1.responseJSON.errors){
                    errors += err1.responseJSON.errors[i] + "\n";
                }
                alert(errors);
            }
        },
        success: function (ans){
            if(ans.info){
                alert(ans.info);
            }

            let lastPath = plfGetCookie('plfLastPath');
            if(!lastPath){
                lastPath = '';
            }
            plf.getData(lastPath);
        }
    })

})

$(document).on('click','.plf-file-item .plf-pop-remove',function(){
    let confirmText = $(this).attr('data-confirm');
    if(!confirm(confirmText)){
        return false;
    }
    let outer = $(this).closest('.plf-file-item');
    let path = $(this).attr('data-path');
    let action = $(this).attr('data-action');
    let token = getPLFToken();
    $.ajax({
        type:'post',
        url:action,
        data:{path:path,_token:token},
        success: function (ans){
            if(ans.success !== undefined){
                outer.remove();
                $(document).trigger('plf-checked')
            }
        }
    })
});

$(document).on('dblclick','.plf-file-item-file',function(){
    let publicPath = $(this).attr('data-publicPath');
    let name = $(this).find('.plf-filename').text();
    let thumb = $(this).find('.plf-file-img img').attr('src');

    if(plf.target.container !== undefined){
        let imagesArr = ['jpg','jpeg','png','webp','svg'];
        let re = /(?:\.([^.]+))?$/;
        let ext = re.exec(publicPath)[1];

        if(imagesArr.includes(ext.toLowerCase())){
            plf.target.insertHtml("<img src='/"+ publicPath +"' />");
        }else{
            let filename = publicPath.replace(/^.*[\\\/]/, '');
            let downloadLink = "<a href='/"+ publicPath +"' download='"+ filename +"'>"+ filename +"</a>";
            plf.target.insertHtml(downloadLink);
        }

    }else{
        plf.target.find('.plf-field-body img').attr('src',thumb);
        plf.target.find('.plf-field-name').text(name);
        plf.target.find('input[type="hidden"]').val(publicPath);
        plf.target.find('input[type="hidden"]').trigger('change');

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
    outer.find('input[type="hidden"]').trigger('change');
    outer.find('.plf-field-body-extension').remove();
});

function getPLFToken(){
    return $(document).find('.plf-outer .plf-token').val() ?? '';
}

$(document).on('dragenter dragover', '.plf-popup-inner', function(e) {
    e.preventDefault();
    e.stopPropagation();
    $(this).addClass('plf-file-drag-adding');
});

$(document).on('dragleave','.plf-popup-inner', function(e) {
    e.preventDefault();
    e.stopPropagation();

    var relatedTarget = e.relatedTarget || e.originalEvent.relatedTarget;

    if (!relatedTarget || !$(this).has(relatedTarget).length) {
        $(this).removeClass('plf-file-drag-adding');
    }
});


$(document).on('drop', '.plf-popup-inner', function(e) {
    e.preventDefault();
    e.stopPropagation();

    let $this = $(this);
    let files = e.originalEvent.dataTransfer.files;

    if (files.length > 0) {
        setTimeout(function() {
            $this.removeClass('plf-file-drag-adding');
            let fileInput = $this.find('#plf-file-input');

            fileInput[0].files = files;

            fileInput.trigger('change');
        }, 100);
    }
});


let plfSelectClickTimer; // Змінна для зберігання таймера

$(document).on('click', '.plf-body-inner .plf-file-item', function () {
    const $this = $(this);

    clearTimeout(plfSelectClickTimer);

    plfSelectClickTimer = setTimeout(function() {
        $this.toggleClass('plf-checked');
        $(document).trigger('plf-checked');
    }, 200);
});


//selection actions
$(document).on('plf-checked',function (){
    if($(document).find('.plf-body-inner .plf-file-item.plf-checked').length){
        $(document).find('.plf-group-btns .plf-files-copy,.plf-group-btns .plf-files-cut,.plf-group-btns .plf-files-remove').show();
    }else{
        $(document).find('.plf-group-btns .plf-files-copy,.plf-group-btns .plf-files-cut,.plf-group-btns .plf-files-remove').hide();
    }
})


$(document).on('click','.plf-group-btns .plf-files-copy, .plf-group-btns .plf-files-cut',function (){
    let $this = $(this);
    let type = $this.attr('data-type');
    let items = getSelectedItems();

    if(type == 'copy'){
        plfSetCookie('plfCopyItems',JSON.stringify(items));
        plfSetCookie('plfCutItems', "", { 'max-age': -1 });
    }else if(type == 'cut'){
        plfSetCookie('plfCutItems',JSON.stringify(items));
        plfSetCookie('plfCopyItems', "", { 'max-age': -1 });
    }else{
        return false;
    }
    showHidePutCancelButton();
    $(document).find('.plf-body-inner .plf-file-item.plf-checked').removeClass('plf-checked');
    $(document).trigger('plf-checked');
})

$(document).on('click','.plf-group-btns .plf-files-cancel',function (){
    plfSetCookie('plfCutItems', "", { 'max-age': -1 });
    plfSetCookie('plfCopyItems', "", { 'max-age': -1 });
    showHidePutCancelButton();
})

$(document).on('click','.plf-files-remove:not(.loading)',function (){
    let confirmText = $(this).attr('data-textconfirm');
    if(!confirm(confirmText)){
        return false;
    }
    let $this = $(this);
    $this.addClass('loading');

    let items = getSelectedItems();
    if(!items){
        return false;
    }
    let token = getPLFToken();
    let url = $(this).attr('data-action');

    $.ajax({
        type:'POST',
        url: url,
        data: {_token:token,items:items},
        complete: function (){
            $this.removeClass('loading');
        },
        success: function(ans){
            if(ans.success !== undefined){
                for(i in items){
                    $(document).find('.plf-body-inner .plf-file-item[data-path="'+ items[i] +'"]').remove();
                }
                $(document).trigger('plf-checked');
            }
        }
    })
})

$(document).on('click','.plf-files-put:not(.loading)',function (){
    let copyItems = plfGetCookie('plfCopyItems');
    let cutItems = plfGetCookie('plfCutItems');
    let $this = $(this);
    $this.addClass('loading');

    if(!copyItems && !cutItems){
        alert('ERROR! No files!')
        return false;
    }
    let token = getPLFToken();
    let items, url;
    if(copyItems){
        items = JSON.parse(copyItems);
        url = $this.attr('data-action-copy');
    }else if(cutItems){
        items = JSON.parse(cutItems);
        url = $this.attr('data-action-move');
    }
    let path = plfGetCookie('plfLastPath');

    $.ajax({
        type: "POST",
        url: url,
        data:{_token:token,items:items,path:path},
        complete: function (){
            $this.removeClass('loading');
        },
        error:function (err){
            console.log(err);
        },
        success:function (ans){
            if(ans.success != undefined){
                plfSetCookie('plfCutItems', "", { 'max-age': -1 });
                plfSetCookie('plfCopyItems', "", { 'max-age': -1 });
                showHidePutCancelButton();

                plf.getData(path);
            }
        }
    });
});

function getSelectedItems(){
    let items = [];
    if($(document).find('.plf-body-inner .plf-file-item.plf-checked').length){
        $(document).find('.plf-body-inner .plf-file-item.plf-checked').each(function (){
            items.push($(this).attr('data-path'));
        })
    }
    return items;
}
function showHidePutCancelButton(){
    let copyItems = plfGetCookie('plfCopyItems');
    let cutItems = plfGetCookie('plfCutItems');

    if(copyItems || cutItems){
        $(document).find('.plf-group-btns .plf-files-cancel,.plf-group-btns .plf-files-put').show();
    }else{
        $(document).find('.plf-group-btns .plf-files-cancel,.plf-group-btns .plf-files-put').hide();
    }

}


$(document).on('click','.plf-fields-multiple-adding-outer',function(){
    let outer = $(this).closest('.plf-fields-multiple-outer');
    outer.find('.plf-fields-multiple-placeholder .plf-field-outer').clone().insertBefore(outer.find('.plf-fields-multiple-adding-outer'));
})

$(document).on('click','.plf-field-delete',function(){
    $(this).closest('.plf-field-outer').remove();
});

//});
