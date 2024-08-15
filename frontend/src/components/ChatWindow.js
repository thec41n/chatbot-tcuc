import React, { useState } from "react";
import ChatInput from "./ChatInput";
import "./ChatWindow.css";

const ChatWindow = () => {
  const [messages, setMessages] = useState([]);

  const handleSendMessage = async (message) => {
    const newMessage = { text: message, sender: "user" };
    setMessages([...messages, newMessage]);

    const response = await fetch("http://localhost:8000/api/messages", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ sender: "user", message: message }),
    });

    const data = await response.json();

    setMessages((prevMessages) => [
      ...prevMessages,
      { text: data.botReply.message, sender: "bot" },
    ]);
  };

  return (
    <div className="chat-window">
      <div className="messages">
        {messages.map((msg, index) => (
          <div key={index} className={`message ${msg.sender}`}>
            {msg.text}
          </div>
        ))}
      </div>
      <ChatInput onSendMessage={handleSendMessage} />
    </div>
  );
};

export default ChatWindow;
