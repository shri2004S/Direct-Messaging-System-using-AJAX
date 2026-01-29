# 💬 AJAX Chat System (NeuroNest Module)

A lightweight, real-time private messaging system built with **PHP**, **MySQL**, and **AJAX**. 

This project was developed as the communication module for  allowing for seamless asynchronous messaging between users (e.g., Doctors and Patients) without page reloads.

## 🚀 Features

* **Real-time Messaging:** Send and receive messages instantly using AJAX requests.
* **JSON API:** All backend endpoints return structured JSON data for easy frontend integration.
* **Private Conversations:** Secure 1-on-1 messaging history.
* **User Status:** "Online" status updates via heartbeat mechanism.
* **Secure:** Session-based authentication checks on every endpoint.

## 🛠️ Tech Stack

* **Backend:** PHP (Vanilla)
* **Database:** MySQL
* **Data Exchange:** JSON
* **Frontend:** JavaScript (Fetch API / AJAX), HTML, CSS

## 📂 API Endpoints

This repository contains the backend API logic:

| File | Description | Method |
| :--- | :--- | :--- |
| `get_users.php` | Fetches a list of available users/contacts (excluding self). | GET |
| `fetch_messages.php` | Retrieves conversation history between the logged-in user and a receiver. | GET |
| `send_message.php` | Handles message insertion and returns the new message object. | POST |
| `update_status.php` | Updates the current user's status to 'online'. | GET/POST |
