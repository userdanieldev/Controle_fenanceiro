CREATE SCHEMA controle_gastos;

CREATE TABLE mes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    ano INT NOT NULL
);


CREATE TABLE categoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL
);


CREATE TABLE movimentacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('Saída', 'Entrada') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data DATE NOT NULL,
    mes_id INT,
    categoria_id INT, 
    FOREIGN KEY (mes_id) REFERENCES mes(id),
    FOREIGN KEY (categoria_id) REFERENCES categoria(id)
);


INSERT INTO mes (nome, ano)
VALUES 
('Janeiro', 2024),
('Fevereiro', 2024),
('Março', 2024),
('Abril', 2024),
('Maio', 2024),
('Junho', 2024),
('Julho', 2024),
('Agosto', 2024),
('Setembro', 2024),
('Outubro', 2024),
('Novembro', 2024);


INSERT INTO categoria (nome)
VALUES 
('Renda Fixa'),
('Renda Extra'),
('Moradia'),
('Alimentação'),
('Entretenimento'),
('Serviços'),
('Educação'),
('Saúde'),
('Lazer'),
('Transporte');


INSERT INTO movimentacoes (nome, tipo, valor, data, mes_id, categoria_id)
VALUES 
-- Janeiro
('Salário', 'Entrada', 5000.00, '2024-01-10', 1, 1),
('Plano de Saúde', 'Saída', 350.00, '2024-01-12', 1, 8),
('Cinema', 'Saída', 50.00, '2024-01-20', 1, 9),
('Internet', 'Saída', 120.00, '2024-01-22', 1, 6),
('Restituição IR', 'Entrada', 800.00, '2024-01-25', 1, 2),

-- Fevereiro
('Salário', 'Entrada', 5000.00, '2024-02-10', 2, 1),
('Energia Elétrica', 'Saída', 200.00, '2024-02-05', 2, 6),
('Roupa', 'Saída', 300.00, '2024-02-10', 2, 4),
('Investimento', 'Entrada', 1000.00, '2024-02-15', 2, 2),
('Jantar', 'Saída', 150.00, '2024-02-20', 2, 9),

-- Março
('Salário', 'Entrada', 5000.00, '2024-03-10', 3, 1),
('Escola', 'Saída', 600.00, '2024-03-05', 3, 7),
('Reembolso', 'Entrada', 300.00, '2024-03-10', 3, 2),
('Streaming', 'Saída', 45.00, '2024-03-15', 3, 6),
('Farmácia', 'Saída', 250.00, '2024-03-25', 3, 8),

-- Abril
('Salário', 'Entrada', 5000.00, '2024-04-10', 4, 1),
('Viagem', 'Saída', 2000.00, '2024-04-03', 4, 9),
('Dividendo', 'Entrada', 300.00, '2024-04-20', 4, 2),

-- Maio
('Salário', 'Entrada', 5000.00, '2024-05-10', 5, 1),
('Manutenção do Carro', 'Saída', 500.00, '2024-05-15', 5, 10),
('Academia', 'Saída', 100.00, '2024-05-20', 5, 9),
('Cursos Online', 'Saída', 400.00, '2024-05-25', 5, 7),

-- Junho
('Salário', 'Entrada', 5000.00, '2024-06-10', 6, 1),
('Compra de Livros', 'Saída', 200.00, '2024-06-05', 6, 7),
('Cinema', 'Saída', 60.00, '2024-06-20', 6, 9),

-- Julho
('Salário', 'Entrada', 5000.00, '2024-07-10', 7, 1),
('Plano de Saúde', 'Saída', 350.00, '2024-07-12', 7, 8),
('Streaming', 'Saída', 45.00, '2024-07-15', 7, 6),

-- Agosto
('Salário', 'Entrada', 5000.00, '2024-08-10', 8, 1),
('Restaurante', 'Saída', 150.00, '2024-08-05', 8, 4),
('Supermercado', 'Saída', 600.00, '2024-08-10', 8, 4),

-- Setembro
('Salário', 'Entrada', 5000.00, '2024-09-10', 9, 1),
('Mensalidade Faculdade', 'Saída', 1200.00, '2024-09-15', 9, 7),

-- Outubro
('Salário', 'Entrada', 5000.00, '2024-10-10', 10, 1),
('Jantar com Amigos', 'Saída', 300.00, '2024-10-05', 10, 9),
('Plano de Saúde', 'Saída', 350.00, '2024-10-12', 10, 8),

-- Novembro
('Salário', 'Entrada', 5000.00, '2024-11-10', 11, 1),
('Black Friday Compras', 'Saída', 1500.00, '2024-11-24', 11, 4),
('Conserto Notebook', 'Saída', 800.00, '2024-11-15', 11, 6);
