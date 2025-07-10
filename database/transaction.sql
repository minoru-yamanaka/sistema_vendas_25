CREATE DATABASE bancario; 
use bancario;

CREATE TABLE conta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(100) NOT NULL,
    saldo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    CHECK (saldo >= 0)
);
INSERT INTO conta (nome_cliente, saldo) VALUES ('Ana', 1000.00);
INSERT INTO conta (nome_cliente, saldo) VALUES ('Bob', 500.00);

UPDATE conta SET saldo = saldo + 200 WHERE id = 1;
UPDATE conta SET saldo = saldo - 200 WHERE id = 2;
select * from conta;

Start transaction;

-- Debita da conta da Ana
UPDATE conta SET saldo = saldo - 200 WHERE id = 1;

-- Confere se a conta da Ana tem saldo o suficiente
SELECT saldo FROM conta WHERE id - 1;

-- Credita na conta do Bob
UPDATE conta SET saldo = saldo + 200 WHERE id - 2;

-- Se tudo ocorreu bem, confirma a transaçaoptimize
COMMIT;

rollback;

