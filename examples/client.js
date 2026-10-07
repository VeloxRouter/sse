const eventSource = new EventSource('/api/stream');

eventSource.addEventListener('notification', function(event) {
    const data = JSON.parse(event.data);
    console.log("New notification:", data);
});

eventSource.onerror = function(error) {
    console.error("SSE connection error. Auto-reconnecting...", error);
};
