function jsSosFallbackCopy2Clipboard( value ) {
    let ta = document.createElement("textarea");
    ta.value = value;

    ta.style.top = "-100";
    ta.style.left = "-100";
    ta.style.position = "fixed";
    ta.style.display = "none";

    document.body.appendChild(ta);
    ta.focus();
    ta.select();

    try {
        if ( document.execCommand('copy') ) {
            alert("Text Copied to clipboard:\n" + value);
        } else  {
            alert("Your browser didn't copy the content.");
        }
    } catch (err) {
        alert("Your browser didn't copy the content. Problem:\n" + err);
    }

    document.body.removeChild(ta);
}

function jsSosCopy2Clipboard( value ) {
   if (navigator.clipboard) {
        navigator.clipboard.writeText(value).then(function () {
            alert("Text Copied to clipboard:\n" + value);
        }, function (err) {
            alert("Your browser didn't copy the content. Problem:\n" + err);
        });
    } else {
        jsSosFallbackCopy2Clipboard(value);
    }
    document.activeElement.blur();
}

function jsCheckNumber(e) {
    let ret = false;
    let cc = 0;
    if (window.event) { // IE
        cc = e.keyCode;
    } else if(e.which) { // Netscape/Firefox/Opera
        cc = e.which;
    } else {
        alert('This browser is not supported.')
    }

    if ( (cc >= 48 && cc <= 57) || (cc == 190) ) {
        ret = true;
    } else if ( cc == 188 ) {
        e.preventDefault();
        e.target.value += '.';
    }

    return ret;
}
