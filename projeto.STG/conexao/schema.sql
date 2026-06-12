SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';


-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;


-- -----------------------------------------------------
-- Table `mydb`.`cursos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`cursos` ;


CREATE TABLE IF NOT EXISTS `mydb`.`cursos` (
  `id_curso` INT NOT NULL AUTO_INCREMENT,
  `nome_curso` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_curso`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`usuarios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`usuarios` ;


CREATE TABLE IF NOT EXISTS `mydb`.`usuarios` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `senha_hash` VARCHAR(255) NOT NULL,
  `cpf` VARCHAR(14) NOT NULL UNIQUE,
  `perfil` ENUM('admin', 'orientador', 'supervisor', 'estagiario') NOT NULL,
  `curso_id` INT NULL,
  PRIMARY KEY (`id_usuario`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`concedentes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`concedentes` ;


CREATE TABLE IF NOT EXISTS `mydb`.`concedentes` (
  `id_concedente` INT NOT NULL AUTO_INCREMENT,
  `nome_instituicao` VARCHAR(150) NOT NULL,
  `cnpj` VARCHAR(18) NULL UNIQUE,
  `tipo` ENUM('empresa', 'escola', 'institucional') NOT NULL,
  `convenio_ativo` TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_concedente`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`estagios`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`estagios` ;


CREATE TABLE IF NOT EXISTS `mydb`.`estagios` (
  `id_estagio` INT NOT NULL AUTO_INCREMENT,
  `estagiario_id` INT NOT NULL,
  `orientador_id` INT NULL,
  `supervisor_id` INT NOT NULL,
  `concedente_id` INT NOT NULL,
  `data_inicio` DATE NOT NULL,
  `data_fim` DATE NOT NULL,
  `status` ENUM('Pendente', 'Ativo', 'Concluido', 'Cancelado') NOT NULL DEFAULT 'Pendente',
  PRIMARY KEY (`id_estagio`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`documentos`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`documentos` ;


CREATE TABLE IF NOT EXISTS `mydb`.`documentos` (
  `id_documento` INT NOT NULL AUTO_INCREMENT,
  `id_estagio` INT NOT NULL,
  `tipo` ENUM('Termo de Compromisso de Estágio Obrigatório', 'Plano de Desenvolvimento do Estágio', 'Relatorio Parcial', 'Relatorio Final', 'Ficha de Frequência de Estágio', 'Ficha de Avaliacao de Estagiário', 'Ficha de Autoavaliação de Estagiário') NOT NULL,
  `url_arquivo` VARCHAR(255) NOT NULL,
  `status_doc` ENUM('Em Analise', 'Aprovado', 'Recusado') NOT NULL DEFAULT 'Em Analise',
  `data_envio` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observacao` TEXT NULL,
  PRIMARY KEY (`id_documento`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- CRIAÇÃO DAS CHAVES ESTRANGEIRAS (RELACIONAMENTOS)
-- -----------------------------------------------------


-- Relacionamento: Usuários pertencem a um Curso
ALTER TABLE `mydb`.`usuarios`
  ADD CONSTRAINT `fk_usuarios_cursos`
  FOREIGN KEY (`curso_id`) REFERENCES `mydb`.`cursos` (`id_curso`)
  ON DELETE SET NULL ON UPDATE CASCADE;


-- Relacionamento: Estágio -> Aluno (Estagiário)
ALTER TABLE `mydb`.`estagios`
  ADD CONSTRAINT `fk_estagios_estagiario`
  FOREIGN KEY (`estagiario_id`) REFERENCES `mydb`.`usuarios` (`id_usuario`)
  ON DELETE RESTRICT ON UPDATE CASCADE;


-- Relacionamento: Estágio -> Professor Orientador
ALTER TABLE `mydb`.`estagios`
  ADD CONSTRAINT `fk_estagios_orientador`
  FOREIGN KEY (`orientador_id`) REFERENCES `mydb`.`usuarios` (`id_usuario`)
  ON DELETE SET NULL ON UPDATE CASCADE;


-- Relacionamento: Estágio -> Supervisor da Empresa/Escola
ALTER TABLE `mydb`.`estagios`
  ADD CONSTRAINT `fk_estagios_supervisor`
  FOREIGN KEY (`supervisor_id`) REFERENCES `mydb`.`usuarios` (`id_usuario`)
  ON DELETE RESTRICT ON UPDATE CASCADE;


-- Relacionamento: Estágio -> Local do Estágio (Concedente)
ALTER TABLE `mydb`.`estagios`
  ADD CONSTRAINT `fk_estagios_concedentes`
  FOREIGN KEY (`concedente_id`) REFERENCES `mydb`.`concedentes` (`id_concedente`)
  ON DELETE RESTRICT ON UPDATE CASCADE;


-- Relacionamento: Documentos pertencem a um Estágio
ALTER TABLE `mydb`.`documentos`
  ADD CONSTRAINT `fk_documentos_estagios`
  FOREIGN KEY (`id_estagio`) REFERENCES `mydb`.`estagios` (`id_estagio`)
  ON DELETE CASCADE ON UPDATE CASCADE;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
