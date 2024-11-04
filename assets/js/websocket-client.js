var userId = jQuery("#af-chat-user_id").val();
console.log(userId);

var receiverId = jQuery("#af-chat-recipient").val();

var conn = new WebSocket("ws://localhost:8080");

conn.onopen = function () {
    console.log("Connection established!");
    // Send the user ID as the initial identification message
    conn.send(JSON.stringify({ type: "identify", user_id: userId }));
};

conn.onmessage = function (e) {
    const data = JSON.parse(e.data);
    const $messagesContainer = jQuery("#af-chat-messages");
    console.log(data);
    console.log(data.sender_id);

    // Only display the message if it's from the current chat partner
    if (data.sender_id === receiverId) {
        const $message = jQuery("<div>").text(data.message);
        $messagesContainer.append($message);
    }
};

jQuery(document).ready(function () {
    const $form = jQuery("#af-chat-form");
    const $input = jQuery("#af-chat-input");

    $form.on("submit", function (event) {
        event.preventDefault();
        const message = $input.val();

        // Send message with sender_id and receiver_id
        conn.send(
            JSON.stringify({
                sender_id: userId,
                receiver_id: receiverId,
                message: message,
            })
        );

        $input.val("");
    });
});
