import React, { useState } from "react";
import ChatInput from "./ChatInput";
import "./ChatWindow.css";

const ChatWindow = () => {
  const [messages, setMessages] = useState([]);
  const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

  const handleSendMessage = async (message) => {
    const newMessage = { text: message, sender: "user" };
    setMessages([...messages, newMessage]);

    try {
      const response = await fetch("http://localhost:8000/botman", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ message: message }),
      });

      const data = await response.json();

      if (data.messages && data.messages[0] && data.messages[0].text) {
        setMessages((prevMessages) => [
          ...prevMessages,
          { text: data.messages[0].text, sender: "bot" },
        ]);
      } else {
        console.error("Unexpected response format:", data);
      }
    } catch (error) {
      console.error("Error sending message:", error);
    }
  };

  return (
    <div className="chat-window">
      <div className="messages">
        {messages.map((msg, index) => (
          <div key={index} className={`message ${msg.sender}`}>
            <span className="sender">
              {msg.sender === "user" ? "User" : "Bot"}
            </span>
            {msg.sender === "bot" ? (
              <div
                className="bot-message"
                dangerouslySetInnerHTML={{ __html: msg.text }}
              />
            ) : (
              <div>{msg.text}</div>
            )}
          </div>
        ))}
      </div>
      <ChatInput onSendMessage={handleSendMessage} />
    </div>
  );
};

export default ChatWindow;
