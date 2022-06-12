let localVideo = document.getElementById('local-video');
let remoteVideo = document.getElementById('remote-video');
// let turnVideo = document.getElementById('turn-video-button');
let ID = localVideo.dataset.sessionId;
let callId = localVideo.dataset.callId;
const callList = [];
let callOptions = {
    'iceServers': [
        {
            url: 'stun:stun.uwork.pro:5349',
            username: "uwork",
            credential: "pass"
        },
        {
            url: "turn:turn.uwork.pro:5349",
            username: "uwork",
            credential: "pass"
        }]
};

var videoState = 1;

var peer = new Peer(ID, {
    host: 'uwork.pro',
    port: 9000,
    path: '/myapp',
    config: callOptions
});

document.onreadystatechange = () => {
    startCall(true, true);
};

// turnVideo.onclick = () => {
//     if (videoState === 1) {
//         videoState = 0;
//         startCall(true, false);
//     } else {
//         videoState = 1;
//         startCall(true, true);
//     }
// };

function startCall(audio, video) {
    initialization(audio, video).then((mediaStream) => {
        callToRemote(peer, mediaStream);
        peer.on('error', (err) => {
            console.log(err);
        });
        peer.on('close', (err) => {
            console.log(err);
        });
        peer.on('call', function (call) {
            call.answer(mediaStream);
            renderRemoteVideo(call);
        });
    });
}

function initialization(audio, video) {
    return new Promise((resolve, reject) => {
        navigator.mediaDevices.getUserMedia({audio: audio, video: video}).then(function (mediaStream) {
            localVideo.srcObject = mediaStream;
            localVideo.onloadedmetadata = function (e) {
                localVideo.play();
                // turnVideo.onclick = () => {
                //     if (videoState === 1) {
                //         videoState = 0;
                //         videoOff(localVideo, mediaStream);
                //     } else {
                //         videoState = 1;
                //         videoOn(localVideo, mediaStream);
                //     }
                // };
                resolve(mediaStream);
            };
        }).catch(function (err) {
            console.log(err.name + ": " + err.message);
        });
    });
}

function callToRemote(peer, mediaStream) {
    getRemotePeerId().then((peerId) => {
        var peerCall = peer.call(peerId, mediaStream);
        if (peerCall) {
            peerCall.on('stop', (e) => {
                console.log(e);
            });
            peerCall.on('stream', function (stream) {
                renderRemoteVideo(peerCall);
            });
        }
    });
}

function getRemotePeerId() {
    return new Promise((resolve, reject) => {
        let timerId = setInterval(() => {
            const xhr = new XMLHttpRequest();
            xhr.responseType = 'json';
            xhr.open("GET", `/user/call/peer-id/${callId}`);
            xhr.onload = () => {
                if (xhr.response.id !== undefined) {
                    resolve(xhr.response.id);
                    clearInterval(timerId);
                }
            };
            xhr.send();
        }, 2000);

    });
}

function renderRemoteVideo(call) {
    setTimeout(function () {
        if (callList.includes(call.remoteStream.id)) {
            return;
        }
        callList.push(call.remoteStream.id);
        remoteVideo.srcObject = call.remoteStream;
        console.log(call.remoteStream);
        remoteVideo.onloadedmetadata = function (e) {
            remoteVideo.play();
        };
    }, 1500);
}

function videoOff(video, stream) {
    stream.getTracks().forEach((track) => {
        if (track.kind === "video") {
            console.log(track, 'off');
            track.enabled = false;
        }
    });
    video.pause();
    video.src = "";
    video.classList.remove('video-on');
    video.classList.add('video-off');
}

function videoOn(video, stream) {
    stream.getTracks().forEach((track) => {
        if (track.kind === "video") {
            console.log(track, 'off');
            track.enabled = true;
        }
    });
    video.srcObject = stream;
    video.play();
    video.classList.remove('video-off');
    video.classList.add('video-on');
}