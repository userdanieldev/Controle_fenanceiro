CREATE SCHEMA controle_gastos;

CREATE TABLE mes (
    id INT AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE movimentacoes (
    id INT AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('Saida', 'Entrada') NOT NULL,
    categoria ENUM('Alimentação', 'Transporte', 'Lazer', 'Saúde', 'Compras', 'Educação', 'Aplicação em Investimentos', 'Serviços', 'Renda', 'Rendimento de Investimentos', 'Renda Extra', 'Doação', 'Prêmio', 'Outros') NOT NULL,
    valor DECIMAL (7,2) NOT NULL,
    data DATE NOT NULL,
    mes_id INT NOT NULL,
    FOREIGN KEY (mes_id) REFERENCES Mes(id),
    PRIMARY KEY (id)
);


INSERT INTO mes (nome)
VALUES 
('Janeiro'),
('Fevereiro'),
('Março'),
('Abril'),
('Maio'),
('Junho'),
('Julho'),
('Agosto'),
('Setembro'),
('Outubro'),
('Novembro');


INSERT INTO movimentacoes (nome, tipo, categoria, valor, data, mes_id)
VALUES 
-- Janeiro
('Salário', 'Entrada', 'Renda', 5000, '2024-01-01', 1),
('Plano de Saúde', 'Saída', 'Saúde', 350.00, '2024-01-12', 1),
('Cinema', 'Saída', 'Lazer', 50.00, '2024-01-20', 1),
('Internet', 'Saída', 'Serviços', 120.00, '2024-01-22', 1),
('Restituição IR', 'Entrada', 'Prêmio', 800.00, '2024-01-25', 1),

-- Fevereiro
('Salário', 'Entrada', 'Renda', 5000.00, '2024-02-10', 2),
('Energia Elétrica', 'Saída', 'Serviços', 200.00, '2024-02-05', 2),
('Roupa', 'Saída', 'Compras', 300.00, '2024-02-10', 2),
('Investimento', 'Entrada', 'Rendimento de Investimentos', 1000.00, '2024-02-15', 2),
('Jantar', 'Saída', 'Lazer', 150.00, '2024-02-20', 2),

-- Dezembro
('Salário', 'Entrada', 'Renda', 5000.00, '2024-03-10', 3),
('Conta de Água', 'Saída', 'Serviços', 168.00, '2024-03-05', 3),
('Roupa', 'Saída', 'Compras', 300.00, '2024-03-10', 3),
('Investimento', 'Entrada', 'Rendimento de Investimentos', 1000.00, '2024-03-15', 3),
('Cinema', 'Saída', 'Lazer', 97, '2024-03-20', 3);