-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS orkuth;
USE orkuth;

-- Criar a tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    nome VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    foto VARCHAR(255)
);

CREATE TABLE amigos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,        -- quem enviou a solicitação
    amigo_id INT NOT NULL,          -- quem recebeu a solicitação
    status ENUM('pendente', 'aceito') DEFAULT 'pendente',
    data_solicitacao DATETIME DEFAULT CURRENT_TIMESTAMP
);

