var es;

function startTask() {
    var config = document.getElementById('clipper').value;
    var archive = document.getElementById('archive').value;
    var archiveUrl = document.getElementById('archiveUrl').value;
    var archiveFile = document.getElementById('archiveFile').value;
    var dbFile = document.getElementById('database');
    var rootFiles = document.getElementById('rootFiles');
    var folders = document.getElementsByName('folders[]');
    var queryString = '?config=' + config;
    queryString += '&archive=' + archive;
    queryString += '&db=' + dbFile.checked;

    if (rootFiles.checked) {
        queryString += '&rootFiles=' + rootFiles.value;
    }

    var folderList = [];
    for (var i = 0; i < folders.length; i++) {
        if (folders.item(i).checked) {
            folderList.push(folders.item(i).value);
        }
    }
    queryString += '&folders=' + folderList;

    es = new EventSource('sse_progress.php' + queryString);

    //a message is received
    var busyCount = 0;

    es.addEventListener('message', function(e) {
        var result = JSON.parse( e.data );
        var pc = 0;

        if (result.type == 'target') {
            pc = 0;
        } else if (result.type == 'busy') {
            pc = result.progress;
// character-based progress indicator
////            busy(busyCount, '+');
            busyCount += 10;
        } else if (result.type == 'reset') {
            addLog('<br />' + (busyCount - 1) + ' files <br />');
            busyCount = 1;
        } else {
            addLog(result.message);
        }

// normal closing
        if(e.lastEventId == 'CLOSE') {
            addLog('Closing');
            es.close();
            var link = '<a href="' + archiveUrl + '">' + archiveFile + '</a>';
            addLog('Download ' + link);
            var pBar = document.getElementById('progressor');
            pBar.value = pBar.max; //max out the progress bar
        }
        else {
            var pBar = document.getElementById('progressor');
            pBar.value = pc;
            var perc = document.getElementById('percentage');
            perc.innerHTML   = Math.floor(pc * 100) + "%";
//            perc.style.width = (Math.floor(pBar.clientWidth * (result.progress/100)) + 15) + 'px';
        }
    });

    es.addEventListener('error', function(e) {
        addLog('Error occurred');
        es.close();
    });
}

function stopTask() {
    es.close();
    addLog('Interrupted');
}

function addLog(message) {
    var r = document.getElementById('results');
    r.innerHTML += message + '<br />';
    r.scrollTop = r.scrollHeight;
}

function busy(bCount, ch) {
    ch = ch || '+';

    var r = document.getElementById('results');
    r.innerHTML += ch;
    if ((bCount) % 700 === 0) {
        r.innerHTML += '<br />';
    }
    r.scrollTop = r.scrollHeight;
}