function plfGetCookie(name) {let matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));return matches ? decodeURIComponent(matches[1]) : undefined;}
function plfSetCookie(name, value, options = {}, callback = null) {options = {path: '/',...options};if (options.expires instanceof Date) { options.expires = options.expires.toUTCString();} let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value); for (let optionKey in options) {updatedCookie += "; " + optionKey;let optionValue = options[optionKey]; if (optionValue !== true) {updatedCookie += "=" + optionValue; } }document.cookie = updatedCookie;if (typeof callback === "function") {callback();} }

// helper: delegated event listener (analog of $(document).on(event, selector, handler))
function plfOn(events, selector, handler) {
    events.split(' ').forEach(function (event) {
        document.addEventListener(event, function (e) {
            let target = e.target.closest(selector);
            if (target && document.contains(target)) {
                handler.call(target, e);
            }
        });
    });
}

// helper: analog of jQuery's slideToggle/slideUp/slideDown
const plfSlideTimers = new WeakMap();
function plfClearSlideTimer(el) {
    let pending = plfSlideTimers.get(el);
    if (pending) {
        window.clearTimeout(pending);
        plfSlideTimers.delete(el);
    }
}
function plfSlideToggle(el) {
    if (!el) return;
    let isHidden = getComputedStyle(el).display === 'none';
    if (isHidden) {
        plfSlideDown(el);
    } else {
        plfSlideUp(el);
    }
}
function plfSlideUp(el) {
    if (!el) return;
    plfClearSlideTimer(el);
    if (getComputedStyle(el).display === 'none') return;
    el.style.overflow = 'hidden';
    el.style.height = el.offsetHeight + 'px';
    el.offsetHeight; // reflow
    el.style.transition = 'height .2s ease';
    el.style.height = '0px';
    let timer = window.setTimeout(function () {
        el.style.display = 'none';
        el.style.removeProperty('height');
        el.style.removeProperty('overflow');
        el.style.removeProperty('transition');
        plfSlideTimers.delete(el);
    }, 200);
    plfSlideTimers.set(el, timer);
}
function plfSlideDown(el) {
    if (!el) return;
    plfClearSlideTimer(el);
    if (getComputedStyle(el).display !== 'none') return;
    el.style.removeProperty('display');
    let display = getComputedStyle(el).display;
    if (display === 'none') display = 'block';
    el.style.display = display;
    let height = el.offsetHeight;
    el.style.overflow = 'hidden';
    el.style.height = '0px';
    el.offsetHeight; // reflow
    el.style.transition = 'height .2s ease';
    el.style.height = height + 'px';
    let timer = window.setTimeout(function () {
        el.style.removeProperty('height');
        el.style.removeProperty('overflow');
        el.style.removeProperty('transition');
        plfSlideTimers.delete(el);
    }, 200);
    plfSlideTimers.set(el, timer);
}

// helper: serialize a form the way jQuery's .serialize() does
function plfSerializeForm(form) {
    return new URLSearchParams(new FormData(form)).toString();
}

// helper: fetch + parse JSON, tolerating non-OK / non-JSON responses instead of
// letting res.json() throw into an unhandled rejection
function plfFetchJson(url, options) {
    return fetch(url, options).then(function (res) {
        return res.json()
            .catch(function () {
                return null;
            })
            .then(function (json) {
                return {ok: res.ok, status: res.status, json: json};
            });
    });
}

function plfAlertErrors(json) {
    if (json && json.errors !== undefined) {
        let errors = "";
        for (let i in json.errors) {
            errors += json.errors[i] + "\n";
        }
        alert(errors);
    }
}

const plfLoader = '<div class="pre-lds-grid"><div class="lds-grid"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>';
const plf = {
    target: false,
    open: function () {
        let plfPopup = "<div class='plf-bg'></div>";
        plfPopup += "<div class='plf-popup'>" +
            "<div class='plf-popup-outer'>" +
            "<button class='plf-close' type='button'>&#10006;</button>" +
            "<div class='plf-popup-inner'></div>" +
            "</div>"
        "</div>";

        document.body.insertAdjacentHTML('beforeend', plfPopup);
    },
    close: function () {
        plf.target = false;
        document.querySelectorAll('.plf-bg,.plf-popup').forEach(function (el) {
            el.remove();
        });
    },
    getData: function (path = '') {
        plfSetCookie('plfLastPath', path);
        let inner = document.querySelector('.plf-popup-inner');
        if (inner) {
            inner.innerHTML = plfLoader;
        }
        fetch('/laravel-files?path=' + path + '&d=' + Date.now(), {
            method: 'get',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
            .then(function (res) {
                return res.text();
            })
            .then(function (ans) {
                let inner = document.querySelector('.plf-popup-inner');
                if (inner) {
                    inner.innerHTML = ans;
                }
                document.dispatchEvent(new CustomEvent('plf-checked'));
                showHidePutCancelButton();
            });
    }
};


// Safety net: these forms are only ever submitted through fetch() from a
// button click. Browsers can implicitly submit a form on Enter in a lone
// text input even when preventDefault() was called on the keypress event,
// so also block the actual 'submit' event as a last resort.
plfOn('submit', '.plf-new-folder-form, .plf-search-form', function (e) {
    e.preventDefault();
    return false;
});

plfOn('click', '.plf-field-body', function (e) {
    plf.target = e.target.closest('.plf-field-outer');
    let lastPath = plfGetCookie('plfLastPath');
    if (!lastPath) {
        lastPath = '';
    }
    plf.open();
    plf.getData(lastPath);
});

plfOn('click', '.plf-close,.plf-bg', function () {
    plf.close();
});

plfOn('click', '.plf-addFolder', function () {
    plfSlideToggle(document.querySelector('.plf-new-folder-pop'));
});
plfOn('click', '.plf-cancelFolder', function () {
    plfSlideUp(document.querySelector('.plf-new-folder-pop'));
    let input = document.querySelector('.plf-new-folder-form input[name="foldername"]');
    if (input) {
        input.value = '';
    }
});
plfOn('click', '.plf-newFolder', function () {
    let form = document.querySelector('.plf-new-folder-form');
    let url = form.getAttribute('action');
    let token = getPLFToken();
    let data = plfSerializeForm(form);
    data += "&_token=" + token;

    fetch(url, {
        method: 'post',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: data
    })
        .then(function (res) {
            if (!res.ok) {
                return;
            }
            let lastPath = plfGetCookie('plfLastPath');
            if (!lastPath) {
                lastPath = '';
            }
            plf.getData(lastPath);
        });
});


plfOn('keypress', '.plf-new-folder-form input[name="foldername"]', function (e) {
    if (e.which == 13) {
        e.preventDefault();
        let btn = this.closest('form').querySelector('.plf-newFolder');
        if (btn) {
            btn.dispatchEvent(new Event('click', {bubbles: true}));
        }
        return false;
    }
});


plfOn('click', '.plf-search', function () {
    plfSlideToggle(document.querySelector('.plf-search-pop'));
});
plfOn('click', '.plf-cancelSearch', function () {
    plfSlideUp(document.querySelector('.plf-search-pop'));
    let lastPath = plfGetCookie('plfLastPath');
    if (!lastPath) {
        lastPath = '';
    }
    plf.getData(lastPath);
});

plfOn('click', '.plf-go-search', function () {
    let form = document.querySelector('.plf-search-form');
    let url = form.getAttribute('action');
    let sInput = form.querySelector('input[name="s"]');
    let s = sInput ? sInput.value : '';
    if (s.length <= 0) {
        let lastPath = plfGetCookie('plfLastPath');
        if (!lastPath) {
            lastPath = '';
        }
        plf.getData(lastPath);
        return false;
    }
    let data = plfSerializeForm(form);
    let token = getPLFToken();
    data += "&_token=" + token;

    plfFetchJson(url, {
        method: 'post',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: data
    })
        .then(function (result) {
            let ans = result.json;
            if (result.ok && ans && ans.success !== undefined) {
                let inner = document.querySelector('.plf-body .plf-body-inner');
                if (inner) {
                    inner.innerHTML = ans.html;
                }
                document.dispatchEvent(new CustomEvent('plf-checked'));
            }
        });
});
plfOn('keypress', '.plf-search-form input[name="s"]', function (e) {
    if (e.which == 13) {
        e.preventDefault();
        let btn = this.closest('form').querySelector('.plf-go-search');
        if (btn) {
            btn.dispatchEvent(new Event('click', {bubbles: true}));
        }
        return false;
    }
});

plfOn('dblclick', '.plf-file-item-dir', function () {
    let path = this.getAttribute('data-path');
    plf.getData(path);
});


plfOn('click', '.plf-path li.plf-path-li', function () {
    let path = this.getAttribute('data-path');
    plf.getData(path);
});


plfOn('click', '.plf-files-form button', function () {
    let parent = this.parentElement;
    let sibling = parent ? Array.prototype.find.call(parent.children, function (child) {
        return child !== this && child.matches('input');
    }, this) : null;
    if (sibling) {
        sibling.dispatchEvent(new MouseEvent('click', {bubbles: true}));
    }
});
plfOn('change', '.plf-files-form input', function () {
    let form = this.closest('form');
    let action = form.getAttribute('action');
    let token = getPLFToken();
    let folder = form.querySelector('input[name="folder"]').value;
    let files = form.querySelector('input[name="files"]');
    let data = new FormData();
    for (let i = 0; i < files.files.length; i++) {
        data.append('file-' + i, files.files[i]);
    }
    data.append('folder', folder);
    data.append('_token', token);

    plfFetchJson(action, {
        method: 'post',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: data
    })
        .then(function (result) {
            let ans = result.json;
            if (ans && ans.rejected && ans.rejected.length) {
                alert('Не завантажено (заборонений тип файлу): ' + ans.rejected.join(', '));
            }
            let lastPath = plfGetCookie('plfLastPath');
            if (!lastPath) {
                lastPath = '';
            }
            plf.getData(lastPath);
        });
});

plfOn('click', '.plf-file-item .plf-pop-rename', function () {
    let promptQuestion = this.getAttribute('data-prompt');
    let item = this.closest('.plf-file-item');
    let filenameEl = item ? item.querySelector('.plf-filename') : null;
    let currentName = filenameEl ? filenameEl.textContent : '';
    if (item && item.classList.contains('plf-file-item-file')) {
        currentName = currentName.replace(/\.[^.]+$/, '');
    }
    let newName = prompt(promptQuestion, currentName);

    if (!newName) {
        return false;
    }

    let path = this.getAttribute('data-path');
    let action = this.getAttribute('data-action');
    let token = getPLFToken();

    plfFetchJson(action, {
        method: 'post',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: new URLSearchParams({_token: token, path: path, newName: newName}).toString()
    })
        .then(function (result) {
            if (!result.ok) {
                plfAlertErrors(result.json);
                return;
            }
            let ans = result.json;
            if (ans && ans.info) {
                alert(ans.info);
            }

            let lastPath = plfGetCookie('plfLastPath');
            if (!lastPath) {
                lastPath = '';
            }
            plf.getData(lastPath);
        });
});

plfOn('click', '.plf-file-item .plf-pop-remove', function () {
    let confirmText = this.getAttribute('data-confirm');
    if (!confirm(confirmText)) {
        return false;
    }
    let outer = this.closest('.plf-file-item');
    let path = this.getAttribute('data-path');
    let action = this.getAttribute('data-action');
    let token = getPLFToken();

    plfFetchJson(action, {
        method: 'post',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: new URLSearchParams({path: path, _token: token}).toString()
    })
        .then(function (result) {
            let ans = result.json;
            if (result.ok && ans && ans.success !== undefined) {
                outer.remove();
                document.dispatchEvent(new CustomEvent('plf-checked'));
            }
        });
});

plfOn('dblclick', '.plf-file-item-file', function () {
    if (!plf.target) {
        plf.close();
        return;
    }
    let publicPath = this.getAttribute('data-publicPath');
    let filenameEl = this.querySelector('.plf-filename');
    let name = filenameEl ? filenameEl.textContent : '';
    let thumbImg = this.querySelector('.plf-file-img img');
    let thumb = thumbImg ? thumbImg.getAttribute('src') : null;

    if (plf.target.container !== undefined) {
        //CKEditor 4
        let imagesArr = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        let re = /(?:\.([^.]+))?$/;
        let ext = re.exec(publicPath)[1];

        if (imagesArr.includes(ext.toLowerCase())) {
            plf.target.insertHtml("<img src='/" + publicPath + "' />");
        } else {
            let filename = publicPath.replace(/^.*[\\\/]/, '');
            let downloadLink = "<a href='/" + publicPath + "' download='" + filename + "'>" + filename + "</a>";
            plf.target.insertHtml(downloadLink);
        }

    } else if (plf.target.root !== undefined && plf.target.root.classList && plf.target.root.classList.contains('ql-editor')) {
        //Quill (ql-editor)
        let imagesArr = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
        let re = /(?:\.([^.]+))?$/;
        let ext = re.exec(publicPath)[1];
        let range = plf.target.getSelection(true);
        let index = range ? range.index : plf.target.getLength();

        if (imagesArr.includes(ext.toLowerCase())) {
            plf.target.insertEmbed(index, 'image', '/' + publicPath, 'user');
            plf.target.setSelection(index + 1, 0, 'user');
        } else {
            let filename = publicPath.replace(/^.*[\\\/]/, '');
            let downloadLink = "<a href='/" + publicPath + "' download='" + filename + "'>" + filename + "</a>";
            let lengthBefore = plf.target.getLength();
            plf.target.clipboard.dangerouslyPasteHTML(index, downloadLink, 'user');
            let insertedLength = plf.target.getLength() - lengthBefore;
            plf.target.setSelection(index + insertedLength, 0, 'user');
        }

    } else {
        let fieldImg = plf.target.querySelector('.plf-field-body img');
        if (fieldImg) {
            fieldImg.setAttribute('src', thumb);
        }
        let fieldName = plf.target.querySelector('.plf-field-name');
        if (fieldName) {
            fieldName.textContent = name;
        }
        let hiddenInput = plf.target.querySelector('input[type="hidden"]');
        if (hiddenInput) {
            hiddenInput.value = publicPath;
        }

        if (this.querySelector('.plf-file-extension')) {
            let existingExt = plf.target.querySelector('.plf-field-body .plf-field-body-extension');
            if (existingExt) {
                existingExt.remove();
            }
            let ext = this.querySelector('.plf-file-extension').textContent;
            let fieldBody = plf.target.querySelector('.plf-field-body');
            if (fieldBody) {
                fieldBody.insertAdjacentHTML('beforeend', "<span class='plf-field-body-extension'>" + ext + "</span>");
            }
        }
    }
    plf.close();
});

plfOn('click', '.plf-field-remove', function () {
    let outer = this.closest('.plf-field-outer');
    let img = outer.querySelector('.plf-field-body img');
    let placeholder = img ? img.getAttribute('data-placeholder') : null;
    if (img) {
        img.setAttribute('src', placeholder);
    }
    let fieldName = outer.querySelector('.plf-field-name');
    if (fieldName) {
        fieldName.textContent = '';
    }
    let hiddenInput = outer.querySelector('input[type="hidden"]');
    if (hiddenInput) {
        hiddenInput.value = '';
    }
    let ext = outer.querySelector('.plf-field-body-extension');
    if (ext) {
        ext.remove();
    }
});

function getPLFToken() {
    let el = document.querySelector('.plf-outer .plf-token');
    return el ? (el.value ?? '') : '';
}

plfOn('dragenter dragover', '.plf-popup-inner', function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.add('plf-file-drag-adding');
});

plfOn('dragleave', '.plf-popup-inner', function (e) {
    e.preventDefault();
    e.stopPropagation();

    let relatedTarget = e.relatedTarget;

    if (!relatedTarget || !this.contains(relatedTarget)) {
        this.classList.remove('plf-file-drag-adding');
    }
});


plfOn('drop', '.plf-popup-inner', function (e) {
    e.preventDefault();
    e.stopPropagation();

    let $this = this;
    let files = e.dataTransfer.files;

    if (files.length > 0) {
        window.setTimeout(function () {
            $this.classList.remove('plf-file-drag-adding');
            let fileInput = $this.querySelector('#plf-file-input');

            fileInput.files = files;

            fileInput.dispatchEvent(new Event('change', {bubbles: true}));
        }, 100);
    }
});


let plfSelectClickTimer; // Змінна для зберігання таймера

plfOn('click', '.plf-body-inner .plf-file-item', function () {
    const $this = this;

    window.clearTimeout(plfSelectClickTimer);

    plfSelectClickTimer = window.setTimeout(function () {
        $this.classList.toggle('plf-checked');
        document.dispatchEvent(new CustomEvent('plf-checked'));
    }, 200);
});


//selection actions
document.addEventListener('plf-checked', function () {
    let btns = document.querySelectorAll('.plf-group-btns .plf-files-copy,.plf-group-btns .plf-files-cut,.plf-group-btns .plf-files-remove');
    if (document.querySelectorAll('.plf-body-inner .plf-file-item.plf-checked').length) {
        btns.forEach(function (el) {
            el.style.display = '';
        });
    } else {
        btns.forEach(function (el) {
            el.style.display = 'none';
        });
    }
});


plfOn('click', '.plf-group-btns .plf-files-copy, .plf-group-btns .plf-files-cut', function () {
    let $this = this;
    let type = $this.getAttribute('data-type');
    let items = getSelectedItems();

    if (type == 'copy') {
        plfSetCookie('plfCopyItems', JSON.stringify(items));
        plfSetCookie('plfCutItems', "", {'max-age': -1});
    } else if (type == 'cut') {
        plfSetCookie('plfCutItems', JSON.stringify(items));
        plfSetCookie('plfCopyItems', "", {'max-age': -1});
    } else {
        return false;
    }
    showHidePutCancelButton();
    document.querySelectorAll('.plf-body-inner .plf-file-item.plf-checked').forEach(function (el) {
        el.classList.remove('plf-checked');
    });
    document.dispatchEvent(new CustomEvent('plf-checked'));
});

plfOn('click', '.plf-group-btns .plf-files-cancel', function () {
    plfSetCookie('plfCutItems', "", {'max-age': -1});
    plfSetCookie('plfCopyItems', "", {'max-age': -1});
    showHidePutCancelButton();
});

plfOn('click', '.plf-files-remove:not(.loading)', function () {
    let confirmText = this.getAttribute('data-textconfirm');
    if (!confirm(confirmText)) {
        return false;
    }
    let $this = this;
    $this.classList.add('loading');

    let items = getSelectedItems();
    if (!items || !items.length) {
        $this.classList.remove('loading');
        return false;
    }
    let token = getPLFToken();
    let url = this.getAttribute('data-action');

    let body = new URLSearchParams();
    body.append('_token', token);
    items.forEach(function (item) {
        body.append('items[]', item);
    });

    plfFetchJson(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: body.toString()
    })
        .then(function (result) {
            let ans = result.json;
            if (result.ok && ans && ans.success !== undefined) {
                items.forEach(function (item) {
                    let el = document.querySelector('.plf-body-inner .plf-file-item[data-path="' + item + '"]');
                    if (el) {
                        el.remove();
                    }
                });
                document.dispatchEvent(new CustomEvent('plf-checked'));
            }
        })
        .finally(function () {
            $this.classList.remove('loading');
        });
});

plfOn('click', '.plf-files-put:not(.loading)', function () {
    let copyItems = plfGetCookie('plfCopyItems');
    let cutItems = plfGetCookie('plfCutItems');
    let $this = this;
    $this.classList.add('loading');

    if (!copyItems && !cutItems) {
        alert('ERROR! No files!');
        $this.classList.remove('loading');
        return false;
    }
    let token = getPLFToken();
    let items, url;
    if (copyItems) {
        items = JSON.parse(copyItems);
        url = $this.getAttribute('data-action-copy');
    } else if (cutItems) {
        items = JSON.parse(cutItems);
        url = $this.getAttribute('data-action-move');
    }
    let path = plfGetCookie('plfLastPath');

    let body = new URLSearchParams();
    body.append('_token', token);
    items.forEach(function (item) {
        body.append('items[]', item);
    });
    body.append('path', path);

    plfFetchJson(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: body.toString()
    })
        .then(function (result) {
            let ans = result.json;
            if (result.ok && ans && ans.success != undefined) {
                plfSetCookie('plfCutItems', "", {'max-age': -1});
                plfSetCookie('plfCopyItems', "", {'max-age': -1});
                showHidePutCancelButton();

                plf.getData(path);
            }
        })
        .catch(function (err) {
            console.log(err);
        })
        .finally(function () {
            $this.classList.remove('loading');
        });
});

function getSelectedItems() {
    let items = [];
    document.querySelectorAll('.plf-body-inner .plf-file-item.plf-checked').forEach(function (el) {
        items.push(el.getAttribute('data-path'));
    });
    return items;
}
function showHidePutCancelButton() {
    let copyItems = plfGetCookie('plfCopyItems');
    let cutItems = plfGetCookie('plfCutItems');

    let btns = document.querySelectorAll('.plf-group-btns .plf-files-cancel,.plf-group-btns .plf-files-put');
    if (copyItems || cutItems) {
        btns.forEach(function (el) {
            el.style.display = '';
        });
    } else {
        btns.forEach(function (el) {
            el.style.display = 'none';
        });
    }
}


plfOn('click', '.plf-fields-multiple-adding-outer', function () {
    let outer = this.closest('.plf-fields-multiple-outer');
    let placeholder = outer.querySelector('.plf-fields-multiple-placeholder .plf-field-outer');
    let addingOuter = outer.querySelector('.plf-fields-multiple-adding-outer');
    if (placeholder && addingOuter) {
        let clone = placeholder.cloneNode(true);
        addingOuter.parentNode.insertBefore(clone, addingOuter);
    }
});

plfOn('click', '.plf-field-delete', function () {
    this.closest('.plf-field-outer').remove();
});
