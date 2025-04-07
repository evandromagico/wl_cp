-- Adicionar colunas para projetista e montador
ALTER TABLE projetos 
ADD COLUMN projetista_id INT,
ADD COLUMN montador_id INT;

-- Adicionar chaves estrangeiras
ALTER TABLE projetos 
ADD FOREIGN KEY (projetista_id) REFERENCES usuarios(id),
ADD FOREIGN KEY (montador_id) REFERENCES usuarios(id); 