
$(document).ready(function () {

    var conn = new WebSocket("ws://localhost:8080");
    var chatForm = $(".chatform");
    var message = chatForm.find("#message");
    var messageList = $(".message-list");


    chatForm.on('submit', function (event) {
        event.preventDefault();
        var msg = message.val();
        var data = { msg: msg };
        conn.send(JSON.stringify(data));

        messageList.prepend("<li>" + message + "</li>");

    });


    conn.onopen = function (event) {
        console.log("Connection Established");
    };

    conn.onmessage = function (event) {
        var data = event.data;
        console.log(data);
        data = JSON.parse(data);


        if (data.type === "offer") {
            peer.setRemoteDescription(new RTCSessionDescription(JSON.parse(event.data)));
            console.log('Setting REMOTE SDP');
            peer.createAnswer({ offerToReceiveVideo: 1 })
                .then((sdp) => {
                    peer.setLocalDescription(sdp);
                    console.log('Setting local SDP');
                    console.log(JSON.stringify(sdp));
                    conn.send(JSON.stringify(sdp));
                });
        } else if (data.type === "answer") {
            peer.setRemoteDescription(new RTCSessionDescription(JSON.parse(event.data)));
            console.log('Setting REMOTE SDP');
        }


        //  messageList.prepend("<li>" + event.data + "</li>");




    };


    //// Peer Connection    
    var streamBtn = $("#stream");
    var callBtn = $("#call");

    var ICE_config = {
        'urls': [

            {
                url: 'turn:numb.viagenie.ca',
                credential: 'muazkh',
                username: 'webrtc@live.com'
            },
            {
                url: 'turn:192.158.29.39:3478?transport=udp',
                credential: 'JZEOEt2V3Qb0y27GRntt2u2PAYA=',
                username: '28224511:1379330808'
            },
            {
                url: 'turn:192.158.29.39:3478?transport=tcp',
                credential: 'JZEOEt2V3Qb0y27GRntt2u2PAYA=',
                username: '28224511:1379330808'
            }
        ]
    }

    let peer = new RTCPeerConnection(ICE_config);
    var candidates = [];

    streamBtn.on('click', startStreaming);
    callBtn.on('click', startCall);



    peer.addEventListener('icecandidate', (e) => {
        if (e.candidate) {
            console.log("ICE CANDIDATE");
            console.log(JSON.stringify(e.candidate));
            candidates.push(e.candidate);
            alert(e.candidate.candidate);

        }
    });

    peer.addEventListener('iceconnectionstatechange', (e) => {
        console.log('ICE STATE CHANGES');
        console.log(e);
    });

    peer.addEventListener('addstream', (e) => {
        remoteVideo.srcObject = e.stream;
        console.log('Remote Stream added');
        console.log(e);

    });



    function startStreaming() {
        console.log("Starting the stream");
        navigator.mediaDevices.getUserMedia({ video: true })
            .then((stream) => {
                localVideo.srcObject = stream;
                stream.getTracks().forEach(function(track){
                    peer.addTrack(track, stream);
                    alert("stream added");
                });
            })
            .catch((error) => {
                console.log("Got the streming error: " + error);
            });
    }


    function startCall() {

        peer.createOffer({ offerToReceiveVideo: 1 })
            .then((sdp) => {
                console.log("LOCAL SDP: (in string form) \n" + JSON.stringify(sdp));
                peer.setLocalDescription(sdp);

                conn.send(JSON.stringify(sdp));
            }).catch((err) => {
                console.log(err);
            });
    }

    function addCandidate() {
        
        console.log("Adding Candidate: " + candidate);
        alicePeer.addIceCandidate(new RTCIceCandidate(candidate));
    }

});
