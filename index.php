
<?php 


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>SDP TEST</title>

    <style>
        video {
            width: 250px;
            height: 200px;
            border: 5px solid #333;
        }
    </style>

</head>

<body>

    <p id="offer"></p>
    <p id="answer"></p>

    <button id="start">Start Streaming</button><button id="create">Offer</button><button id="send">Answer</button>
    <button id="end" onclick="endCall()">End Call</button><br><br>
    <video id="localVideo" autoplay></video>
    <video id="remoteVideo" autoplay></video>
    <br>

        
    <script src="https://webrtc.github.io/adapter/adapter-latest.js"></script>
    <script>
        
        // var conn = new WebSocket("wss://localhost:8080");
		var conn = new WebSocket("wss://echo.websocket.org");
		
        conn.onopen = function(event) {
            console.log("Connection Established");
        };



        var offer = document.getElementById("offer");
        var answer = document.getElementById("answer");
        var create = document.getElementById("create");
        var send = document.getElementById("send");
        var local = document.getElementById("localVideo");
        var remote = document.getElementById("remoteVideo");
        var start = document.getElementById("start");
        var area = document.getElementById("area");
        var end = document.getElementById("end");

        create.disabled = true;
        send.disabled = true;
        end.disabled = true;


        var offersdp = null;
        var answersdp = null;


        var pc_config = null;

        // var pc_config = {
        //     "iceServers": [{
        //         urls: 'stun:stun.l.google.com:19302',
        //         'credential': '[YOUR CREDENTIAL]',
        //         'username': '[USERNAME]'
        //     }]
        // }
        var servers = {
            'iceServers': [{
                'urls': 'stun:stun.services.mozilla.com'
            }, {
                'urls': 'stun:stun.l.google.com:19302'
            }, {
                'urls': 'turn:numb.viagenie.ca',
                'credential': '111222',
                'username': 'scottpiligrim2018@yopmail.com'
            }]
        };

        var pc = new RTCPeerConnection(servers);

        pc.addEventListener('icecandidate', handlePeerConnection);

        pc.addEventListener('addstream', (e) => {
            remoteVideo.srcObject = e.stream;
            console.log('Remote Stream added');
            console.log(e);

        });

        start.onclick = () => {
            console.log("Starting the stream");
            navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then((stream) => {
                    localVideo.srcObject = stream;
                    stream.getTracks().forEach(function(track) {
                        pc.addTrack(track, stream);
                        create.disabled = false;

                    });
                })
                .catch((error) => {
                    console.log("Got the streming error: " + error);
                });
        }


        create.onclick = () => {

            pc.createOffer({
                    offerToReceiveVideo: 1
                })
                .then((sdp) => {
                    pc.setLocalDescription(sdp);
                    conn.send(JSON.stringify(sdp));
                    console.log(JSON.stringify(sdp));
                    end.disabled = false;
                });

        }

        send.onclick = () => {
            pc.createAnswer({
                    offerToReceiveVideo: 1
                })
                .then((sdp) => {

                    pc.setLocalDescription(sdp);
                    var sdp = JSON.stringify(sdp);
                    conn.send(sdp);
                    end.disabled = false;
                    console.log(sdp);

                });
        }

        conn.onmessage = (event) => {
            var data = event.data;

            data = JSON.parse(data);
            if (data.type === "offer" || data.type === "answer") {
                if (data.type === "offer") {
                    alert("Someone wants to talk to you... Click answer");
                    send.disabled = false;
                }
                setRemote(data);
                console.log("Remote description set");


            } else {
                pc.addIceCandidate(new RTCIceCandidate(JSON.parse(event.data)));
                console.log("Adding Candidate");
            }

        }

        function setRemote(desc) {
            pc.setRemoteDescription(new RTCSessionDescription(desc));
        }

        function handlePeerConnection(event) {

            const peerConnection = event.target;
            const iceCandidate = event.candidate;

            if (iceCandidate) {
                const newIceCandidate = new RTCIceCandidate(iceCandidate);
                conn.send(JSON.stringify(iceCandidate));
                console.log(JSON.stringify(iceCandidate));
               
            }
        }

        function endCall() {
            pc.close();
            pc = null;

            end.disabled = true;
            answer.disabled = true;
            send.disabled = true;
        }
    </script>

</body>

</html>
