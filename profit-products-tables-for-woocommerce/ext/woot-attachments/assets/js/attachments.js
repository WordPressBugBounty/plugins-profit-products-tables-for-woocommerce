document.addEventListener('table23-html-drawn', e => {
    DataTable23.do_not_print.push('attachments'); //attachments is cell data-key
}, false);


var woot_audio_is_play = false;
function woot_audio_end(audio) {

    if (audio.closest('table')) {
        let self_is_found = false;
        audio.closest('table').querySelectorAll('audio').forEach(function (a) {

            if (self_is_found && woot_audio_is_play === false) {
                woot_audio_is_play = true;
                a.play();
                return true;
            }

            if (a === audio && !self_is_found) {
                self_is_found = true;
            }
        });
    } else {
        if (audio.nextSibling.nextSibling) {
            audio.nextSibling.nextSibling.play();
        }
    }

    woot_audio_is_play = false;
}

function woot_audio_play(audio) {
    if (audio.closest('table')) {
        audio.closest('table').querySelectorAll('audio').forEach(function (a) {
            if (a !== audio) {
                a.pause();
            }
        });
    } else {
        audio.parentNode.querySelectorAll('audio').forEach(function (a) {
            if (a !== audio) {
                a.pause();
            }
        });
    }
}
