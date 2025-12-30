const mqttConfig = {
    // Alamat Host Broker (Gunakan protokol WebSocket: ws:// atau wss://)
    // Contoh: 'wss://broker.emqx.io:8084/mqtt' atau 'ws://broker.hivemq.com:8000/mqtt'
    host: "wss://broker.emqx.io:8084/mqtt", 
    
    // Kredensial (Biarkan kosong jika broker public tidak memerlukannya)
    username: "", 
    password: "",
    
    // Topic yang akan disubscribe/publish
    topic: "stikom/iot/dashboard/#",
    
    // ID Client unik
    clientId: "dashboard_" + Math.random().toString(16).substr(2, 8)
};
