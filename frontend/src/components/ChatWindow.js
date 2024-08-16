import React, { useState, useEffect } from "react";
import ChatInput from "./ChatInput";
import "./ChatWindow.css";

const ChatWindow = () => {
  const [messages, setMessages] = useState([]);

  useEffect(() => {
    loadPreviousSession();
  }, []);

  const loadPreviousSession = async () => {
    try {
      const response = await fetch("http://localhost:8000/botman", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ message: "load_previous_session" }),
      });

      const data = await response.json();

      if (data.messages) {
        setMessages(data.messages);
      } else {
        console.error("Unexpected response format:", data);
      }
    } catch (error) {
      console.error("Error loading previous session:", error);
    }
  };

  const handleSendMessage = async (message) => {
    const newMessage = { text: message, sender: "user" };
    setMessages([...messages, newMessage]);

    if (message.toLowerCase() === "hapus chat") {
      setMessages((prevMessages) => [
        ...prevMessages,
        { text: "Semua riwayat chat telah dihapus.", sender: "bot" },
      ]);
      setTimeout(() => setMessages([]), 3000);
    }

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
        if (message.toLowerCase() !== "hapus chat") {
          setMessages((prevMessages) => [
            ...prevMessages,
            { text: data.messages[0].text, sender: "bot" },
          ]);
        }
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
