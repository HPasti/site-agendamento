<?php
session_start();
require_once 'auth.php';
requireDirector();
$pdo = getPDO();

// --- Inserir horarios padrão se não existirem ---
$horarios_completos = [
    ['Segunda', '6º01', '1ª', 'GEO - LUANA'],
    ['Segunda', '6º01', '2ª', 'ELETIVA'],
    ['Segunda', '6º01', '3ª', 'ELETIVA'],
    ['Segunda', '6º01', '4ª', 'LP - LUCILENE'],
    ['Segunda', '6º01', '5ª', 'HIS - GLASIANE'],
    ['Segunda', '6º01', '6ª', 'MAT - KATIA'],
    ['Segunda', '6º01', '7ª', 'LI - MÔNICA'],
    ['Segunda', '6º02', '1ª', 'HIS - GLASIANE'],
    ['Segunda', '6º02', '2ª', 'ELETIVA'],
    ['Segunda', '6º02', '3ª', 'ELETIVA'],
    ['Segunda', '6º02', '4ª', 'GEO - LUANA'],
    ['Segunda', '6º02', '5ª', 'ARTE - LUCIMERY'],
    ['Segunda', '6º02', '6ª', 'LP - LUCILENE'],
    ['Segunda', '6º02', '7ª', 'E O - CAROLINE'],
    ['Segunda', '6º03', '1ª', 'LP - LUCILENE'],
    ['Segunda', '6º03', '2ª', 'ELETIVA'],
    ['Segunda', '6º03', '3ª', 'ELETIVA'],
    ['Segunda', '6º03', '4ª', 'MAT - KATIA'],
    ['Segunda', '6º03', '5ª', 'LI - MÔNICA'],
    ['Segunda', '6º03', '6ª', 'GEO - LUANA'],
    ['Segunda', '6º03', '7ª', 'CIENCIAS - JUSSARA'],
    ['Segunda', '7º01', '1ª', 'MAT - KATIA'],
    ['Segunda', '7º01', '2ª', 'ELETIVA'],
    ['Segunda', '7º01', '3ª', 'ELETIVA'],
    ['Segunda', '7º01', '4ª', 'ARTE - LUCIMERY'],
    ['Segunda', '7º01', '5ª', 'GEO - LUANA'],
    ['Segunda', '7º01', '6ª', 'CIÊNCIAS - JOÃO'],
    ['Segunda', '7º01', '7ª', 'LP - LUCILENE'],
    ['Segunda', '7º02', '1ª', 'E FIS - JAQUELINE'],
    ['Segunda', '7º02', '2ª', 'ELETIVA'],
    ['Segunda', '7º02', '3ª', 'ELETIVA'],
    ['Segunda', '7º02', '4ª', 'E FIS - JAQUELINE'],
    ['Segunda', '7º02', '5ª', 'MAT - KATIA'],
    ['Segunda', '7º02', '6ª', 'ARTE - LUCIMERY'],
    ['Segunda', '7º02', '7ª', 'MAT - KATIA'],
    ['Segunda', '8º01', '1ª', 'LP - RAIMARA'],
    ['Segunda', '8º01', '2ª', 'ELETIVA'],
    ['Segunda', '8º01', '3ª', 'ELETIVA'],
    ['Segunda', '8º01', '4ª', 'MAT - GERALDO'],
    ['Segunda', '8º01', '5ª', 'PE CIENCIAS - JOAO'],
    ['Segunda', '8º01', '6ª', 'LI - MÔNICA'],
    ['Segunda', '8º01', '7ª', 'ARTE - LUCIMERY'],
    ['Segunda', '8º02', '1ª', 'CIÊNCIAS - JOÃO'],
    ['Segunda', '8º02', '2ª', 'ELETIVA'],
    ['Segunda', '8º02', '3ª', 'ELETIVA'],
    ['Segunda', '8º02', '4ª', 'LP - RAIMARA'],
    ['Segunda', '8º02', '5ª', 'PV - CAROLINE'],
    ['Segunda', '8º02', '6ª', 'LP - RAIMARA'],
    ['Segunda', '8º02', '7ª', 'PE CIENCIAS - JOAO'],
    ['Segunda', '8º03', '1ª', 'LI - MÔNICA'],
    ['Segunda', '8º03', '2ª', 'ELETIVA'],
    ['Segunda', '8º03', '3ª', 'ELETIVA'],
    ['Segunda', '8º03', '4ª', 'HIS - GLASIANE'],
    ['Segunda', '8º03', '5ª', 'LP - RAIMARA'],
    ['Segunda', '8º03', '6ª', 'E FIS - LETÍCIA'],
    ['Segunda', '8º03', '7ª', 'E FIS - LETÍCIA'],
    ['Segunda', '9º01', '1ª', 'MAT - WALAS'],
    ['Segunda', '9º01', '2ª', 'ELETIVA'],
    ['Segunda', '9º01', '3ª', 'ELETIVA'],
    ['Segunda', '9º01', '4ª', 'MAT - WALAS'],
    ['Segunda', '9º01', '5ª', 'LP - WILMEIKA'],
    ['Segunda', '9º01', '6ª', 'P CLUBE - GISLENE'],
    ['Segunda', '9º01', '7ª', 'HIS - CARMINHA'],
    ['Segunda', '9º02', '1ª', 'LP - WILMEIKA'],
    ['Segunda', '9º02', '2ª', 'ELETIVA'],
    ['Segunda', '9º02', '3ª', 'ELETIVA'],
    ['Segunda', '9º02', '4ª', 'INGLÊS - GISLENE'],
    ['Segunda', '9º02', '5ª', 'EO - GERALDO'],
    ['Segunda', '9º02', '6ª', 'CIENCIAS - ADILSON'],
    ['Segunda', '9º02', '7ª', 'AP - CLEU'],
    ['Segunda', '9º03', '1ª', 'HIS - CARMINHA'],
    ['Segunda', '9º03', '2ª', 'ELETIVA'],
    ['Segunda', '9º03', '3ª', 'ELETIVA'],
    ['Segunda', '9º03', '4ª', 'PROJ VIDA - RAIANE'],
    ['Segunda', '9º03', '5ª', 'MAT - WALAS'],
    ['Segunda', '9º03', '6ª', 'MAT - WALAS'],
    ['Segunda', '9º03', '7ª', 'E.O RAIMARA'],
    ['Segunda', '1ºI01ENERG', '1ª', 'SOC - RAIANE'],
    ['Segunda', '1ºI01ENERG', '2ª', 'ELETIVA'],
    ['Segunda', '1ºI01ENERG', '3ª', 'ELETIVA'],
    ['Segunda', '1ºI01ENERG', '4ª', 'PROJ DE VIDA - TIAO'],
    ['Segunda', '1ºI01ENERG', '5ª', 'E FIS - JAQUELINE'],
    ['Segunda', '1ºI01ENERG', '6ª', 'E.O CLEU'],
    ['Segunda', '1ºI01ENERG', '7ª', 'LP - WILMEIKA'],
    ['Segunda', '2ºI01ENERG', '1ª', 'BIO - JUSSARA'],
    ['Segunda', '2ºI01ENERG', '2ª', 'ELETIVA'],
    ['Segunda', '2ºI01ENERG', '3ª', 'ELETIVA'],
    ['Segunda', '2ºI01ENERG', '4ª', 'HIS - CARMINHA'],
    ['Segunda', '2ºI01ENERG', '5ª', 'MAT - DELTIANE'],
    ['Segunda', '2ºI01ENERG', '6ª', 'MAT SOC - GERALDO'],
    ['Segunda', '2ºI01ENERG', '7ª', 'PROJ DE VIDA - TIAO'],
    ['Segunda', '3ºI01ENERG', '1ª', 'LP - MARIA DULCE'],
    ['Segunda', '3ºI01ENERG', '2ª', 'ELETIVA'],
    ['Segunda', '3ºI01ENERG', '3ª', 'ELETIVA'],
    ['Segunda', '3ºI01ENERG', '4ª', 'E.O WILMEIKA'],
    ['Segunda', '3ºI01ENERG', '5ª', 'F O DE ENERGIA - ADILSON'],
    ['Segunda', '3ºI01ENERG', '6ª', 'LP - MARIA DULCE'],
    ['Segunda', '3ºI01ENERG', '7ª', 'P.EXP - WALAS'],
    ['Segunda', '1ºEMI', '1ª', 'ARTE - LUCIMERY'],
    ['Segunda', '1ºEMI', '2ª', 'ELETIVA'],
    ['Segunda', '1ºEMI', '3ª', 'ELETIVA'],
    ['Segunda', '1ºEMI', '4ª', 'LP - MARIA DULCE'],
    ['Segunda', '1ºEMI', '5ª', 'QUÍMICA - KAMILA'],
    ['Segunda', '1ºEMI', '6ª', 'A L PROG - LUAN'],
    ['Segunda', '1ºEMI', '7ª', 'QUÍMICA - KAMILA'],
    ['Segunda', '2ºEMI', '1ª', 'APP WEB - LUAN'],
    ['Segunda', '2ºEMI', '2ª', 'ELETIVA'],
    ['Segunda', '2ºEMI', '3ª', 'ELETIVA'],
    ['Segunda', '2ºEMI', '4ª', 'E.O CLEU'],
    ['Segunda', '2ºEMI', '5ª', 'L.P.A WEB - LUAN'],
    ['Segunda', '2ºEMI', '6ª', 'MAT - DELTIANE'],
    ['Segunda', '2ºEMI', '7ª', 'LP - MARIA DULCE'],
    ['Segunda', '3ºEMI', '1ª', 'BIO - ADILSON'],
    ['Segunda', '3ºEMI', '2ª', 'ELETIVA'],
    ['Segunda', '3ºEMI', '3ª', 'ELETIVA'],
    ['Segunda', '3ºEMI', '4ª', 'P W DES - LUAN'],
    ['Segunda', '3ºEMI', '5ª', 'LP - MARIA DULCE'],
    ['Segunda', '3ºEMI', '6ª', 'PROJ DE VIDA - TIAO'],
    ['Segunda', '3ºEMI', '7ª', 'P W DES - LUAN'],

    ['Terça', '6º01', '1ª', 'LP - LUCILENE'],
    ['Terça', '6º01', '2ª', 'CIENCIAS - JUSSARA'],
    ['Terça', '6º01', '3ª', 'LI - MÔNICA'],
    ['Terça', '6º01', '4ª', 'MAT - KATIA'],
    ['Terça', '6º01', '5ª', 'ARTE - LUCIMERY'],
    ['Terça', '6º01', '6ª', 'E FIS - LETÍCIA'],
    ['Terça', '6º01', '7ª', 'E FIS - LETÍCIA'],
    ['Terça', '6º02', '1ª', 'LI - MÔNICA'],
    ['Terça', '6º02', '2ª', 'LP - LUCILENE'],
    ['Terça', '6º02', '3ª', 'P CIENT - JERRY'],
    ['Terça', '6º02', '4ª', 'P EXP - JUSSARA'],
    ['Terça', '6º02', '5ª', 'LP - LUCILENE'],
    ['Terça', '6º02', '6ª', 'P CLUBE - MÔNICA'],
    ['Terça', '6º02', '7ª', 'MAT - KATIA'],
    ['Terça', '6º03', '1ª', 'CIENCIAS - JUSSARA'],
    ['Terça', '6º03', '2ª', 'MAT - KATIA'],
    ['Terça', '6º03', '3ª', 'LP - LUCILENE'],
    ['Terça', '6º03', '4ª', 'ARTE CAROLAINE'],
    ['Terça', '6º03', '5ª', 'LI - MÔNICA'],
    ['Terça', '6º03', '6ª', 'MAT - KATIA'],
    ['Terça', '6º03', '7ª', 'LP - LUCILENE'],
    ['Terça', '7º01', '1ª', 'E FIS - JAQUELINE'],
    ['Terça', '7º01', '2ª', 'E FIS - JAQUELINE'],
    ['Terça', '7º01', '3ª', 'MAT - KATIA'],
    ['Terça', '7º01', '4ª', 'P CIENTÍFICO - KAMILA'],
    ['Terça', '7º01', '5ª', 'MAT - KATIA'],
    ['Terça', '7º01', '6ª', 'LP - LUCILENE'],
    ['Terça', '7º01', '7ª', 'LI - MÔNICA'],
    ['Terça', '7º02', '1ª', 'MAT - KATIA'],
    ['Terça', '7º02', '2ª', 'CIÊNCIAS - JOÃO'],
    ['Terça', '7º02', '3ª', 'CIÊNCIAS - JOÃO'],
    ['Terça', '7º02', '4ª', 'LP - LUCILENE'],
    ['Terça', '7º02', '5ª', 'PV - CAROLINE'],
    ['Terça', '7º02', '6ª', 'P CIENTÍFICO - KAMILA'],
    ['Terça', '7º02', '7ª', 'E O - CAROLINE'],
    ['Terça', '8º01', '1ª', 'ARTE - LUCIMERY'],
    ['Terça', '8º01', '2ª', 'MAT - GERALDO'],
    ['Terça', '8º01', '3ª', 'E.O RAIMARA'],
    ['Terça', '8º01', '4ª', 'MAT - GERALDO'],
    ['Terça', '8º01', '5ª', 'CIÊNCIAS - JOÃO'],
    ['Terça', '8º01', '6ª', 'LP - RAIMARA'],
    ['Terça', '8º01', '7ª', 'P CIENT - JERRY'],
    ['Terça', '8º02', '1ª', 'LP - RAIMARA'],
    ['Terça', '8º02', '2ª', 'LI - MÔNICA'],
    ['Terça', '8º02', '3ª', 'ARTE - LUCIMERY'],
    ['Terça', '8º02', '4ª', 'P CLUBE - MÔNICA'],
    ['Terça', '8º02', '5ª', 'MAT - GERALDO'],
    ['Terça', '8º02', '6ª', 'MAT - GERALDO'],
    ['Terça', '8º02', '7ª', 'E.O RAIMARA'],
    ['Terça', '8º03', '1ª', 'CIÊNCIAS - JOÃO'],
    ['Terça', '8º03', '2ª', 'ARTE - LUCIMERY'],
    ['Terça', '8º03', '3ª', 'E.O JAQUELINE'],
    ['Terça', '8º03', '4ª', 'PE CIENCIAS - JOAO'],
    ['Terça', '8º03', '5ª', 'LP - RAIMARA'],
    ['Terça', '8º03', '6ª', 'P CIENT - JERRY'],
    ['Terça', '8º03', '7ª', 'MAT - GERALDO'],
    ['Terça', '9º01', '1ª', 'CIENCIAS - ADILSON'],
    ['Terça', '9º01', '2ª', 'LP - WILMEIKA'],
    ['Terça', '9º01', '3ª', 'MAT - WALAS'],
    ['Terça', '9º01', '4ª', 'ARTE - LUCIMERY'],
    ['Terça', '9º01', '5ª', 'P EXP CIEN ADILSON'],
    ['Terça', '9º01', '6ª', 'LP - WILMEIKA'],
    ['Terça', '9º01', '7ª', 'INGLÊS - GISLENE'],
    ['Terça', '9º02', '1ª', 'MAT - WALAS'],
    ['Terça', '9º02', '2ª', 'MAT - WALAS'],
    ['Terça', '9º02', '3ª', 'LP - WILMEIKA'],
    ['Terça', '9º02', '4ª', 'E FIS - JAQUELINE'],
    ['Terça', '9º02', '5ª', 'E FIS - JAQUELINE'],
    ['Terça', '9º02', '6ª', 'INGLÊS - GISLENE'],
    ['Terça', '9º02', '7ª', 'CIENCIAS - ADILSON'],
    ['Terça', '9º03', '1ª', 'LP - WILMEIKA'],
    ['Terça', '9º03', '2ª', 'E.O RAIMARA'],
    ['Terça', '9º03', '3ª', 'INGLÊS - GISLENE'],
    ['Terça', '9º03', '4ª', 'LP - WILMEIKA'],
    ['Terça', '9º03', '5ª', 'P CLUBE - GISLENE'],
    ['Terça', '9º03', '6ª', 'CIENCIAS - ADILSON'],
    ['Terça', '9º03', '7ª', 'MAT - WALAS'],
    ['Terça', '1ºI01ENERG', '1ª', 'MAT - GERALDO'],
    ['Terça', '1ºI01ENERG', '2ª', 'INGLÊS - GISLENE'],
    ['Terça', '1ºI01ENERG', '3ª', 'MAT - GERALDO'],
    ['Terça', '1ºI01ENERG', '4ª', 'P EXP CIEN NAT JERRY'],
    ['Terça', '1ºI01ENERG', '5ª', 'QUÍMICA - KAMILA'],
    ['Terça', '1ºI01ENERG', '6ª', 'ARTE - LUCIMERY'],
    ['Terça', '1ºI01ENERG', '7ª', 'BIO - JUSSARA'],
    ['Terça', '2ºI01ENERG', '1ª', 'FIS M ENERGE - JERRY'],
    ['Terça', '2ºI01ENERG', '2ª', 'MAT - DELTIANE'],
    ['Terça', '2ºI01ENERG', '3ª', 'QUÍMICA - KAMILA'],
    ['Terça', '2ºI01ENERG', '4ª', 'LP - MARIA DULCE'],
    ['Terça', '2ºI01ENERG', '5ª', 'LP - MARIA DULCE'],
    ['Terça', '2ºI01ENERG', '6ª', 'MAT - DELTIANE'],
    ['Terça', '2ºI01ENERG', '7ª', 'ARTE - LUCIMERY'],
    ['Terça', '3ºI01ENERG', '1ª', 'INGLÊS - GISLENE'],
    ['Terça', '3ºI01ENERG', '2ª', 'QUÍMICA - KAMILA'],
    ['Terça', '3ºI01ENERG', '3ª', 'LP - MARIA DULCE'],
    ['Terça', '3ºI01ENERG', '4ª', 'BIO - ADILSON'],
    ['Terça', '3ºI01ENERG', '5ª', 'E.O WILMEIKA'],
    ['Terça', '3ºI01ENERG', '6ª', 'MAT - WALAS'],
    ['Terça', '3ºI01ENERG', '7ª', 'MAT ENERGIA - KAMILA'],
    ['Terça', '1ºEMI', '1ª', 'MAT - DELTIANE'],
    ['Terça', '1ºEMI', '2ª', 'LP - MARIA DULCE'],
    ['Terça', '1ºEMI', '3ª', 'P EXP - PETERSON'],
    ['Terça', '1ºEMI', '4ª', 'INGLÊS - GISLENE'],
    ['Terça', '1ºEMI', '5ª', 'H S SEG - JERRY'],
    ['Terça', '1ºEMI', '6ª', 'LP - MARIA DULCE'],
    ['Terça', '1ºEMI', '7ª', 'MAT - DELTIANE'],
    ['Terça', '2ºEMI', '1ª', 'LP - MARIA DULCE'],
    ['Terça', '2ºEMI', '2ª', 'FÍSICA - JERRY'],
    ['Terça', '2ºEMI', '3ª', 'BIO - JUSSARA'],
    ['Terça', '2ºEMI', '4ª', 'IOT - DEVILSON'],
    ['Terça', '2ºEMI', '5ª', 'P EXP - PETERSON'],
    ['Terça', '2ºEMI', '6ª', 'C DIG - PETERSON'],
    ['Terça', '2ºEMI', '7ª', 'INT REDES - DEVILSON'],
    ['Terça', '3ºEMI', '1ª', 'A P SIST - PETERSON'],
    ['Terça', '3ºEMI', '2ª', 'A P SIST - PETERSON'],
    ['Terça', '3ºEMI', '3ª', 'BIO - ADILSON'],
    ['Terça', '3ºEMI', '4ª', 'MAT - WALAS'],
    ['Terça', '3ºEMI', '5ª', 'ARQ S P R - DEVILSON'],
    ['Terça', '3ºEMI', '6ª', 'ARQ S P R - DEVILSON'],
    ['Terça', '3ºEMI', '7ª', 'LP - MARIA DULCE'],

    ['Quarta', '6º01', '1ª', 'P CLUBE - MÔNICA'],
    ['Quarta', '6º01', '2ª', 'E O - CAROLINE'],
    ['Quarta', '6º01', '3ª', 'HIS - GLASIANE'],
    ['Quarta', '6º01', '4ª', 'LP - LUCILENE'],
    ['Quarta', '6º01', '5ª', 'ARTE - LUCIMERY'],
    ['Quarta', '6º01', '6ª', 'LP - LUCILENE'],
    ['Quarta', '6º01', '7ª', 'GEO - LUANA'],
    ['Quarta', '6º02', '1ª', 'PROJ VIDA - RAIANE'],
    ['Quarta', '6º02', '2ª', 'AP - CLEU'],
    ['Quarta', '6º02', '3ª', 'ARTE - LUCIMERY'],
    ['Quarta', '6º02', '4ª', 'LI - MÔNICA'],
    ['Quarta', '6º02', '5ª', 'HIS - GLASIANE'],
    ['Quarta', '6º02', '6ª', 'E O - CAROLINE'],
    ['Quarta', '6º02', '7ª', 'CIENCIAS - JUSSARA'],
    ['Quarta', '6º03', '1ª', 'AP - CLEU'],
    ['Quarta', '6º03', '2ª', 'LP - LUCILENE'],
    ['Quarta', '6º03', '3ª', 'GEO - LUANA'],
    ['Quarta', '6º03', '4ª', 'E FIS - LETÍCIA'],
    ['Quarta', '6º03', '5ª', 'E FIS - LETÍCIA'],
    ['Quarta', '6º03', '6ª', 'GEO - LUANA'],
    ['Quarta', '6º03', '7ª', 'HIS - GLASIANE'],
    ['Quarta', '7º01', '1ª', 'E O - CAROLINE'],
    ['Quarta', '7º01', '2ª', 'LI - MÔNICA'],
    ['Quarta', '7º01', '3ª', 'LP - LUCILENE'],
    ['Quarta', '7º01', '4ª', 'ARTE - LUCIMERY'],
    ['Quarta', '7º01', '5ª', 'P CLUBE - GISLENE'],
    ['Quarta', '7º01', '6ª', 'HIS - GLASIANE'],
    ['Quarta', '7º01', '7ª', 'LP - LUCILENE'],
    ['Quarta', '7º02', '1ª', 'LP - LUCILENE'],
    ['Quarta', '7º02', '2ª', 'GEO - LUANA'],
    ['Quarta', '7º02', '3ª', 'LI - MÔNICA'],
    ['Quarta', '7º02', '4ª', 'HIS - GLASIANE'],
    ['Quarta', '7º02', '5ª', 'LP - LUCILENE'],
    ['Quarta', '7º02', '6ª', 'P CLUBE - GISLENE'],
    ['Quarta', '7º02', '7ª', 'PV - CAROLINE'],
    ['Quarta', '8º01', '1ª', 'GEO - LUANA'],
    ['Quarta', '8º01', '2ª', 'HIS - GLASIANE'],
    ['Quarta', '8º01', '3ª', 'E.O RAIMARA'],
    ['Quarta', '8º01', '4ª', 'PV - CAROLINE'],
    ['Quarta', '8º01', '5ª', 'PV - CAROLINE'],
    ['Quarta', '8º01', '6ª', 'LP - RAIMARA'],
    ['Quarta', '8º01', '7ª', 'CIÊNCIAS - JOÃO'],
    ['Quarta', '8º02', '1ª', 'HIS - GLASIANE'],
    ['Quarta', '8º02', '2ª', 'ARTE - LUCIMERY'],
    ['Quarta', '8º02', '3ª', 'AP - CLEU'],
    ['Quarta', '8º02', '4ª', 'LP - RAIMARA'],
    ['Quarta', '8º02', '5ª', 'GEO - LUANA'],
    ['Quarta', '8º02', '6ª', 'LI - MÔNICA'],
    ['Quarta', '8º02', '7ª', 'E.O RAIMARA'],
    ['Quarta', '8º03', '1ª', 'LP - RAIMARA'],
    ['Quarta', '8º03', '2ª', 'LP - RAIMARA'],
    ['Quarta', '8º03', '3ª', 'PV - CAROLINE'],
    ['Quarta', '8º03', '4ª', 'GEO - LUANA'],
    ['Quarta', '8º03', '5ª', 'LI - MÔNICA'],
    ['Quarta', '8º03', '6ª', 'CIÊNCIAS - JOÃO'],
    ['Quarta', '8º03', '7ª', 'P CLUBE - MÔNICA'],
    ['Quarta', '9º01', '1ª', 'E FIS - JAQUELINE'],
    ['Quarta', '9º01', '2ª', 'E FIS - JAQUELINE'],
    ['Quarta', '9º01', '3ª', 'INGLÊS - GISLENE'],
    ['Quarta', '9º01', '4ª', 'GEO - FRANCIANO'],
    ['Quarta', '9º01', '5ª', 'E.O RAIMARA'],
    ['Quarta', '9º01', '6ª', 'HIS - CARMINHA'],
    ['Quarta', '9º01', '7ª', 'PROJ VIDA - RAIANE'],
    ['Quarta', '9º02', '1ª', 'P CLUBE - GISLENE'],
    ['Quarta', '9º02', '2ª', 'LP - WILMEIKA'],
    ['Quarta', '9º02', '3ª', 'HIS - CARMINHA'],
    ['Quarta', '9º02', '4ª', 'PROJ VIDA - RAIANE'],
    ['Quarta', '9º02', '5ª', 'GEO - FRANCIANO'],
    ['Quarta', '9º02', '6ª', 'LP - WILMEIKA'],
    ['Quarta', '9º02', '7ª', 'ARTE - LUCIMERY'],
    ['Quarta', '9º03', '1ª', 'LP - WILMEIKA'],
    ['Quarta', '9º03', '2ª', 'HIS - CARMINHA'],
    ['Quarta', '9º03', '3ª', 'GEO - FRANCIANO'],
    ['Quarta', '9º03', '4ª', 'LP - WILMEIKA'],
    ['Quarta', '9º03', '5ª', 'AP - CLEU'],
    ['Quarta', '9º03', '6ª', 'ARTE - LUCIMERY'],
    ['Quarta', '9º03', '7ª', 'CIENCIAS - ADILSON'],
    ['Quarta', '1ºI01ENERG', '1ª', 'HIS - CARMINHA'],
    ['Quarta', '1ºI01ENERG', '2ª', 'FILOSOFIA - TIÃO'],
    ['Quarta', '1ºI01ENERG', '3ª', 'LP - WILMEIKA'],
    ['Quarta', '1ºI01ENERG', '4ª', 'INGLÊS - GISLENE'],
    ['Quarta', '1ºI01ENERG', '5ª', 'LP - WILMEIKA'],
    ['Quarta', '1ºI01ENERG', '6ª', 'GEO - FRANCIANO'],
    ['Quarta', '1ºI01ENERG', '7ª', 'P. EXP MAT - GERALDO'],
    ['Quarta', '2ºI01ENERG', '1ª', 'GEO - FRANCIANO'],
    ['Quarta', '2ºI01ENERG', '2ª', 'INGLÊS - GISLENE'],
    ['Quarta', '2ºI01ENERG', '3ª', 'E FIS - JAQUELINE'],
    ['Quarta', '2ºI01ENERG', '4ª', 'LP - MARIA DULCE'],
    ['Quarta', '2ºI01ENERG', '5ª', 'HIS - CARMINHA'],
    ['Quarta', '2ºI01ENERG', '6ª', 'PROJ DE VIDA - TIAO'],
    ['Quarta', '2ºI01ENERG', '7ª', 'E.O CLEU'],
    ['Quarta', '3ºI01ENERG', '1ª', 'LP - MARIA DULCE'],
    ['Quarta', '3ºI01ENERG', '2ª', 'GEO - FRANCIANO'],
    ['Quarta', '3ºI01ENERG', '3ª', 'PROJ DE VIDA - TIAO'],
    ['Quarta', '3ºI01ENERG', '4ª', 'HIS - CARMINHA'],
    ['Quarta', '3ºI01ENERG', '5ª', 'P. INST - M. DULCE'],
    ['Quarta', '3ºI01ENERG', '6ª', 'SOC - RAIANE'],
    ['Quarta', '3ºI01ENERG', '7ª', 'FIS M ENERGE - JERRY'],
    ['Quarta', '1ºEMI', '1ª', 'ARTE - LUCIMERY'],
    ['Quarta', '1ºEMI', '2ª', 'SOC - RAIANE'],
    ['Quarta', '1ºEMI', '3ª', 'S OP - HEITOR'],
    ['Quarta', '1ºEMI', '4ª', 'FILOSOFIA - TIÃO'],
    ['Quarta', '1ºEMI', '5ª', 'SOC - RAIANE'],
    ['Quarta', '1ºEMI', '6ª', 'FÍSICA - JERRY'],
    ['Quarta', '1ºEMI', '7ª', 'INGLÊS - GISLENE'],
    ['Quarta', '2ºEMI', '1ª', 'B. DADOS - HEITOR'],
    ['Quarta', '2ºEMI', '2ª', 'B. DADOS - HEITOR'],
    ['Quarta', '2ºEMI', '3ª', 'LP - MARIA DULCE'],
    ['Quarta', '2ºEMI', '4ª', 'C DIG - PETERSON'],
    ['Quarta', '2ºEMI', '5ª', 'QUÍMICA - KAMILA'],
    ['Quarta', '2ºEMI', '6ª', 'BIO - JUSSARA'],
    ['Quarta', '2ºEMI', '7ª', 'GEO - FRANCIANO'],
    ['Quarta', '3ºEMI', '1ª', 'P EMP - JOSÉ V'],
    ['Quarta', '3ºEMI', '2ª', 'LP - MARIA DULCE'],
    ['Quarta', '3ºEMI', '3ª', 'P EMP - JOSÉ V'],
    ['Quarta', '3ºEMI', '4ª', 'D SISTEMAS - HEITOR'],
    ['Quarta', '3ºEMI', '5ª', 'D. GAMES - PETERSON'],
    ['Quarta', '3ºEMI', '6ª', 'D. GAMES - PETERSON'],
    ['Quarta', '3ºEMI', '7ª', 'HIS - CARMINHA'],

    ['Quinta', '6º01', '1ª', 'CIENCIAS - JUSSARA'],
    ['Quinta', '6º01', '2ª', 'MAT - KATIA'],
    ['Quinta', '6º01', '3ª', 'PROJ VIDA - RAIANE'],
    ['Quinta', '6º01', '4ª', 'P CIENT - JERRY'],
    ['Quinta', '6º01', '5ª', 'GEO - LUANA'],
    ['Quinta', '6º01', '6ª', 'MAT - KATIA'],
    ['Quinta', '6º01', '7ª', 'P EXP - JUSSARA'],
    ['Quinta', '6º02', '1ª', 'MAT - KATIA'],
    ['Quinta', '6º02', '2ª', 'GEO - LUANA'],
    ['Quinta', '6º02', '3ª', 'HIS - GLASIANE'],
    ['Quinta', '6º02', '4ª', 'CIENCIAS - JUSSARA'],
    ['Quinta', '6º02', '5ª', 'MAT - KATIA'],
    ['Quinta', '6º02', '6ª', 'E FIS - LETÍCIA'],
    ['Quinta', '6º02', '7ª', 'E FIS - LETÍCIA'],
    ['Quinta', '6º03', '1ª', 'HIS - GLASIANE'],
    ['Quinta', '6º03', '2ª', 'CIENCIAS - JUSSARA'],
    ['Quinta', '6º03', '3ª', 'P EXP - JUSSARA'],
    ['Quinta', '6º03', '4ª', 'PROJ VIDA - RAIANE'],
    ['Quinta', '6º03', '5ª', 'P CIENT - JERRY'],
    ['Quinta', '6º03', '6ª', 'ARTE CAROLAINE'],
    ['Quinta', '6º03', '7ª', 'MAT - KATIA'],
    ['Quinta', '7º01', '1ª', 'PV - CAROLINE'],
    ['Quinta', '7º01', '2ª', 'ER/ AP - CLEU/ RAIANE'],
    ['Quinta', '7º01', '3ª', 'CIÊNCIAS - JOÃO'],
    ['Quinta', '7º01', '4ª', 'MAT - KATIA'],
    ['Quinta', '7º01', '5ª', 'HIS - GLASIANE'],
    ['Quinta', '7º01', '6ª', 'GEO - LUANA'],
    ['Quinta', '7º01', '7ª', 'E O - CAROLINE'],
    ['Quinta', '7º02', '1ª', 'ER/ AP - CLEU/ RAIANE'],
    ['Quinta', '7º02', '2ª', 'CIÊNCIAS - JOÃO'],
    ['Quinta', '7º02', '3ª', 'MAT - KATIA'],
    ['Quinta', '7º02', '4ª', 'PE CIENCIAS - JOAO'],
    ['Quinta', '7º02', '5ª', 'E O - CAROLINE'],
    ['Quinta', '7º02', '6ª', 'HIS - GLASIANE'],
    ['Quinta', '7º02', '7ª', 'GEO - LUANA'],
    ['Quinta', '8º01', '1ª', 'CIÊNCIAS - JOÃO'],
    ['Quinta', '8º01', '2ª', 'HIS - GLASIANE'],
    ['Quinta', '8º01', '3ª', 'GEO - LUANA'],
    ['Quinta', '8º01', '4ª', 'E FIS - LETÍCIA'],
    ['Quinta', '8º01', '5ª', 'E FIS - LETÍCIA'],
    ['Quinta', '8º01', '6ª', 'AP - CLEU'],
    ['Quinta', '8º01', '7ª', 'MAT - GERALDO'],
    ['Quinta', '8º02', '1ª', 'MAT - GERALDO'],
    ['Quinta', '8º02', '2ª', 'MAT - GERALDO'],
    ['Quinta', '8º02', '3ª', 'P CIENT - JERRY'],
    ['Quinta', '8º02', '4ª', 'GEO - LUANA'],
    ['Quinta', '8º02', '5ª', 'CIÊNCIAS - JOÃO'],
    ['Quinta', '8º02', '6ª', 'CIÊNCIAS - JOÃO'],
    ['Quinta', '8º02', '7ª', 'HIS - GLASIANE'],
    ['Quinta', '8º03', '1ª', 'GEO - LUANA'],
    ['Quinta', '8º03', '2ª', 'PV - CAROLINE'],
    ['Quinta', '8º03', '3ª', 'AP - CLEU'],
    ['Quinta', '8º03', '4ª', 'HIS - GLASIANE'],
    ['Quinta', '8º03', '5ª', 'MAT - GERALDO'],
    ['Quinta', '8º03', '6ª', 'MAT - GERALDO'],
    ['Quinta', '8º03', '7ª', 'CIÊNCIAS - JOÃO'],
    ['Quinta', '9º01', '1ª', 'GEO - FRANCIANO'],
    ['Quinta', '9º01', '2ª', 'CIENCIAS - ADILSON'],
    ['Quinta', '9º01', '3ª', 'P CIENTÍFICO - KAMILA'],
    ['Quinta', '9º01', '4ª', 'HIS - CARMINHA'],
    ['Quinta', '9º01', '5ª', 'MAT - WALAS'],
    ['Quinta', '9º01', '6ª', 'PROJ VIDA - RAIANE'],
    ['Quinta', '9º01', '7ª', 'GEO - FRANCIANO'],
    ['Quinta', '9º02', '1ª', 'MAT - WALAS'],
    ['Quinta', '9º02', '2ª', 'GEO - FRANCIANO'],
    ['Quinta', '9º02', '3ª', 'HIS - CARMINHA'],
    ['Quinta', '9º02', '4ª', 'CIENCIAS - ADILSON'],
    ['Quinta', '9º02', '5ª', 'P EXP CIEN ADILSON'],
    ['Quinta', '9º02', '6ª', 'P CIENTÍFICO - KAMILA'],
    ['Quinta', '9º02', '7ª', 'PROJ VIDA - RAIANE'],
    ['Quinta', '9º03', '1ª', 'HIS - CARMINHA'],
    ['Quinta', '9º03', '2ª', 'MAT - WALAS'],
    ['Quinta', '9º03', '3ª', 'CIENCIAS - ADILSON'],
    ['Quinta', '9º03', '4ª', 'P CIENTÍFICO - KAMILA'],
    ['Quinta', '9º03', '5ª', 'GEO - FRANCIANO'],
    ['Quinta', '9º03', '6ª', 'MAT - WALAS'],
    ['Quinta', '9º03', '7ª', 'P EXP CIEN ADILSON'],
    ['Quinta', '1ºI01ENERG', '1ª', 'PROJ DE VIDA - TIAO'],
    ['Quinta', '1ºI01ENERG', '2ª', 'HIS - CARMINHA'],
    ['Quinta', '1ºI01ENERG', '3ª', 'MAT - GERALDO'],
    ['Quinta', '1ºI01ENERG', '4ª', 'MAT - GERALDO'],
    ['Quinta', '1ºI01ENERG', '5ª', 'E.O CLEU'],
    ['Quinta', '1ºI01ENERG', '6ª', 'BIO - JUSSARA'],
    ['Quinta', '1ºI01ENERG', '7ª', 'FÍSICA - JERRY'],
    ['Quinta', '2ºI01ENERG', '1ª', 'QUÍMICA - KAMILA'],
    ['Quinta', '2ºI01ENERG', '2ª', 'P EXP CIEN NAT JERRY'],
    ['Quinta', '2ºI01ENERG', '3ª', 'P EXP MAT- DELTIANE'],
    ['Quinta', '2ºI01ENERG', '4ª', 'GEO - FRANCIANO'],
    ['Quinta', '2ºI01ENERG', '5ª', 'BIO - JUSSARA'],
    ['Quinta', '2ºI01ENERG', '6ª', 'FIS M ENERGE - JERRY'],
    ['Quinta', '2ºI01ENERG', '7ª', 'MAT - DELTIANE'],
    ['Quinta', '3ºI01ENERG', '1ª', 'BIO - ADILSON'],
    ['Quinta', '3ºI01ENERG', '2ª', 'MAT ENERGIA - KAMILA'],
    ['Quinta', '3ºI01ENERG', '3ª', 'MAT - WALAS'],
    ['Quinta', '3ºI01ENERG', '4ª', 'MAT - WALAS'],
    ['Quinta', '3ºI01ENERG', '5ª', 'MAT SOCIEDADE- DELTIANE'],
    ['Quinta', '3ºI01ENERG', '6ª', 'PROJ DE VIDA - TIAO'],
    ['Quinta', '3ºI01ENERG', '7ª', 'HIS - CARMINHA'],
    ['Quinta', '1ºEMI', '1ª', 'FÍSICA - JERRY'],
    ['Quinta', '1ºEMI', '2ª', 'FILOSOFIA - TIÃO'],
    ['Quinta', '1ºEMI', '3ª', 'PROJ DE VIDA - TIAO'],
    ['Quinta', '1ºEMI', '4ª', 'MAT - DELTIANE'],
    ['Quinta', '1ºEMI', '5ª', 'S OP - HEITOR'],
    ['Quinta', '1ºEMI', '6ª', 'MAT - DELTIANE'],
    ['Quinta', '1ºEMI', '7ª', 'E.O CLEU'],
    ['Quinta', '2ºEMI', '1ª', 'MAT - DELTIANE'],
    ['Quinta', '2ºEMI', '2ª', 'MAT - DELTIANE'],
    ['Quinta', '2ºEMI', '3ª', 'GEO - FRANCIANO'],
    ['Quinta', '2ºEMI', '4ª', 'PROJ DE VIDA - TIAO'],
    ['Quinta', '2ºEMI', '5ª', 'HIS - CARMINHA'],
    ['Quinta', '2ºEMI', '6ª', 'HIS - CARMINHA'],
    ['Quinta', '2ºEMI', '7ª', 'QUÍMICA - KAMILA'],
    ['Quinta', '3ºEMI', '1ª', 'D SISTEMAS - HEITOR'],
    ['Quinta', '3ºEMI', '2ª', 'D SISTEMAS - HEITOR'],
    ['Quinta', '3ºEMI', '3ª', 'L P ORIENT - PETERSON'],
    ['Quinta', '3ºEMI', '4ª', 'L P ORIENT - PETERSON'],
    ['Quinta', '3ºEMI', '5ª', 'PROJ DE VIDA - TIAO'],
    ['Quinta', '3ºEMI', '6ª', 'GEO - FRANCIANO'],
    ['Quinta', '3ºEMI', '7ª', 'MAT - WALAS'],

    ['Sexta', '6º01', '1ª', 'LP - LUCILENE'],
    ['Sexta', '6º01', '2ª', 'HIS - GLASIANE'],
    ['Sexta', '6º01', '3ª', 'MAT - KATIA'],
    ['Sexta', '6º01', '4ª', 'CIENCIAS - JUSSARA'],
    ['Sexta', '6º01', '5ª', 'E O - CAROLINE'],
    ['Sexta', '6º01', '6ª', 'AP - CLEU'],
    ['Sexta', '6º01', '7ª', 'PROJ VIDA - RAIANE'],
    ['Sexta', '6º02', '1ª', 'GEO - LUANA'],
    ['Sexta', '6º02', '2ª', 'LP - LUCILENE'],
    ['Sexta', '6º02', '3ª', 'CIENCIAS - JUSSARA'],
    ['Sexta', '6º02', '4ª', 'MAT - KATIA'],
    ['Sexta', '6º02', '5ª', 'PROJ VIDA - RAIANE'],
    ['Sexta', '6º02', '6ª', 'MAT - KATIA'],
    ['Sexta', '6º02', '7ª', 'LP - LUCILENE'],
    ['Sexta', '6º03', '1ª', 'PROJ VIDA - RAIANE'],
    ['Sexta', '6º03', '2ª', 'E O - CAROLINE'],
    ['Sexta', '6º03', '3ª', 'P CLUBE - MÔNICA'],
    ['Sexta', '6º03', '4ª', 'LP - LUCILENE'],
    ['Sexta', '6º03', '5ª', 'MAT - KATIA'],
    ['Sexta', '6º03', '6ª', 'E O - CAROLINE'],
    ['Sexta', '6º03', '7ª', 'HIS - GLASIANE'],
    ['Sexta', '7º01', '1ª', 'MAT - KATIA'],
    ['Sexta', '7º01', '2ª', 'PE CIENCIAS - JOAO'],
    ['Sexta', '7º01', '3ª', 'LP - LUCILENE'],
    ['Sexta', '7º01', '4ª', 'CIÊNCIAS - JOÃO'],
    ['Sexta', '7º01', '5ª', 'HIS - GLASIANE'],
    ['Sexta', '7º01', '6ª', 'GEO - LUANA'],
    ['Sexta', '7º01', '7ª', 'PV - CAROLINE'],
    ['Sexta', '7º02', '1ª', 'HIS - GLASIANE'],
    ['Sexta', '7º02', '2ª', 'MAT - KATIA'],
    ['Sexta', '7º02', '3ª', 'GEO - LUANA'],
    ['Sexta', '7º02', '4ª', 'ARTE - LUCIMERY'],
    ['Sexta', '7º02', '5ª', 'LP - LUCILENE'],
    ['Sexta', '7º02', '6ª', 'LP - LUCILENE'],
    ['Sexta', '7º02', '7ª', 'LI - MÔNICA'],
    ['Sexta', '8º01', '1ª', 'LP - RAIMARA'],
    ['Sexta', '8º01', '2ª', 'LI - MÔNICA'],
    ['Sexta', '8º01', '3ª', 'HIS - GLASIANE'],
    ['Sexta', '8º01', '4ª', 'GEO - LUANA'],
    ['Sexta', '8º01', '5ª', 'MAT - GERALDO'],
    ['Sexta', '8º01', '6ª', 'P CLUBE - MÔNICA'],
    ['Sexta', '8º01', '7ª', 'LP - RAIMARA'],
    ['Sexta', '8º02', '1ª', 'MAT - GERALDO'],
    ['Sexta', '8º02', '2ª', 'GEO - LUANA'],
    ['Sexta', '8º02', '3ª', 'PV - CAROLINE'],
    ['Sexta', '8º02', '4ª', 'HIS - GLASIANE'],
    ['Sexta', '8º02', '5ª', 'LP - RAIMARA'],
    ['Sexta', '8º02', '6ª', 'E FIS - LETÍCIA'],
    ['Sexta', '8º02', '7ª', 'E FIS - LETÍCIA'],
    ['Sexta', '8º03', '1ª', 'ARTE - LUCIMERY'],
    ['Sexta', '8º03', '2ª', 'LP - RAIMARA'],
    ['Sexta', '8º03', '3ª', 'E.O JAQUELINE'],
    ['Sexta', '8º03', '4ª', 'MAT - GERALDO'],
    ['Sexta', '8º03', '5ª', 'GEO - LUANA'],
    ['Sexta', '8º03', '6ª', 'HIS - GLASIANE'],
    ['Sexta', '8º03', '7ª', 'MAT - GERALDO'],
    ['Sexta', '9º01', '1ª', 'LP - WILMEIKA'],
    ['Sexta', '9º01', '2ª', 'ARTE - LUCIMERY'],
    ['Sexta', '9º01', '3ª', 'LP - WILMEIKA'],
    ['Sexta', '9º01', '4ª', 'E.O RAIMARA'],
    ['Sexta', '9º01', '5ª', 'MAT - WALAS'],
    ['Sexta', '9º01', '6ª', 'CIENCIAS - ADILSON'],
    ['Sexta', '9º01', '7ª', 'AP - CLEU'],
    ['Sexta', '9º02', '1ª', 'MAT - WALAS'],
    ['Sexta', '9º02', '2ª', 'MAT - WALAS'],
    ['Sexta', '9º02', '3ª', 'EO - GERALDO'],
    ['Sexta', '9º02', '4ª', 'GEO - FRANCIANO'],
    ['Sexta', '9º02', '5ª', 'LP - WILMEIKA'],
    ['Sexta', '9º02', '6ª', 'ARTE - LUCIMERY'],
    ['Sexta', '9º02', '7ª', 'HIS - CARMINHA'],
    ['Sexta', '9º03', '1ª', 'E FIS - JAQUELINE'],
    ['Sexta', '9º03', '2ª', 'E FIS - JAQUELINE'],
    ['Sexta', '9º03', '3ª', 'ARTE - LUCIMERY'],
    ['Sexta', '9º03', '4ª', 'PROJ VIDA - RAIANE'],
    ['Sexta', '9º03', '5ª', 'INGLÊS - GISLENE'],
    ['Sexta', '9º03', '6ª', 'LP - WILMEIKA'],
    ['Sexta', '9º03', '7ª', 'GEO - FRANCIANO'],
    ['Sexta', '1ºI01ENERG', '1ª', 'E.O CLEU'],
    ['Sexta', '1ºI01ENERG', '2ª', 'QUÍMICA - KAMILA'],
    ['Sexta', '1ºI01ENERG', '3ª', 'GEO - FRANCIANO'],
    ['Sexta', '1ºI01ENERG', '4ª', 'LP - WILMEIKA'],
    ['Sexta', '1ºI01ENERG', '5ª', 'FÍSICA - JERRY'],
    ['Sexta', '1ºI01ENERG', '6ª', 'MAT - GERALDO'],
    ['Sexta', '1ºI01ENERG', '7ª', 'LP - WILMEIKA'],
    ['Sexta', '2ºI01ENERG', '1ª', 'EO F ENERGIA FRANCIANO'],
    ['Sexta', '2ºI01ENERG', '2ª', 'MAT SOC - GERALDO'],
    ['Sexta', '2ºI01ENERG', '3ª', 'FÍSICA - JERRY'],
    ['Sexta', '2ºI01ENERG', '4ª', 'E.O CLEU'],
    ['Sexta', '2ºI01ENERG', '5ª', 'LP - MARIA DULCE'],
    ['Sexta', '2ºI01ENERG', '6ª', 'EO F ENERGIA FRANCIANO'],
    ['Sexta', '2ºI01ENERG', '7ª', 'FÍSICA - JERRY'],
    ['Sexta', '3ºI01ENERG', '1ª', 'FÍSICA - JERRY'],
    ['Sexta', '3ºI01ENERG', '2ª', 'GEO - FRANCIANO'],
    ['Sexta', '3ºI01ENERG', '3ª', 'MAT - WALAS'],
    ['Sexta', '3ºI01ENERG', '4ª', 'MAT - WALAS'],
    ['Sexta', '3ºI01ENERG', '5ª', 'FILOSOFIA - TIÃO'],
    ['Sexta', '3ºI01ENERG', '6ª', 'LP - MARIA DULCE'],
    ['Sexta', '3ºI01ENERG', '7ª', 'D TECNICO LUCIMERY'],
    ['Sexta', '1ºEMI', '1ª', 'LP - MARIA DULCE'],
    ['Sexta', '1ºEMI', '2ª', 'LP - MARIA DULCE'],
    ['Sexta', '1ºEMI', '3ª', 'A L PROG - LUAN'],
    ['Sexta', '1ºEMI', '4ª', 'E FIS - JAQUELINE'],
    ['Sexta', '1ºEMI', '5ª', 'E FIS - JAQUELINE'],
    ['Sexta', '1ºEMI', '6ª', 'MAT - DELTIANE'],
    ['Sexta', '1ºEMI', '7ª', 'A L PROG - LUAN'],
    ['Sexta', '2ºEMI', '1ª', 'IOT - DEVILSON'],
    ['Sexta', '2ºEMI', '2ª', 'FÍSICA - JERRY'],
    ['Sexta', '2ºEMI', '3ª', 'LP - MARIA DULCE'],
    ['Sexta', '2ºEMI', '4ª', 'INT REDES DEVILSON'],
    ['Sexta', '2ºEMI', '5ª', 'APP WEB - LUAN'],
    ['Sexta', '2ºEMI', '6ª', 'L.P.A WEB - LUAN'],
    ['Sexta', '2ºEMI', '7ª', 'MAT - DELTIANE'],
    ['Sexta', '3ºEMI', '1ª', 'P EXP - PETERSON'],
    ['Sexta', '3ºEMI', '2ª', 'ARQ S P R - DEVILSON'],
    ['Sexta', '3ºEMI', '3ª', 'L P ORIENT - PETERSON'],
    ['Sexta', '3ºEMI', '4ª', 'HIS - CARMINHA'],
    ['Sexta', '3ºEMI', '5ª', 'GEO - FRANCIANO'],
    ['Sexta', '3ºEMI', '6ª', 'MAT - WALAS'],
    ['Sexta', '3ºEMI', '7ª', 'E.O WALAS'],
];

// --- PREPARAR CONSULTAS ---
$check = $pdo->prepare("
    SELECT id FROM horarios 
    WHERE dia = ? AND turma = ? AND aula = ?
");

$insert = $pdo->prepare("
    INSERT INTO horarios (dia, turma, aula, disciplina) 
    VALUES (?, ?, ?, ?)
");

// --- INSERIR SE NÃO EXISTIR ---
foreach ($horarios_completos as $h) {

    list($dia, $turma, $aula, $disciplina) = $h;

    // Verificar se já existe
    $check->execute([$dia, $turma, $aula]);

    if ($check->rowCount() == 0) {
        // Inserir
        $insert->execute([$dia, $turma, $aula, $disciplina]);
    }
}

// Turmas
$turmas = ['6º01','6º02','6º03','7º01','7º02','8º01','8º02','8º03','9º01','9º02','9º03','1ºI01ENERG','2ºI01ENERG','3ºI01ENERG', '1ºEMI', '2ºEMI', '3ºEMI'];

// Dias e aulas
$dias = ['Segunda','Terça','Quarta','Quinta','Sexta'];
$aulas = ['1ª','2ª','3ª','4ª','5ª','6ª','7ª'];

// Disciplinas e cores (mantida a ordem original, cores ajustadas para visual equilibrado)
$cores = [
    'GEO - LUANA' => '#3cb371','HIS - GLASIANE' => '#4a90e2','LP - LUCILENE' => '#ffb400',' MAT SOC - GERALDO' => '#ff7043','P. EXP MAT - GERALDO' => '#e75480','P. INST - M. DULCE' => '#ba68c8','EO F ENERGIA FRANCIANO' => '#d166ff','INT REDES DEVILSON' => '#00b894', 
    'MAT - KATIA' => '#ff6f61','E FIS - JAQUELINE' => '#00bcd4','MAT - GERALDO' => '#ff9800','E.O WILMEIKA' => '#b26cf2','F O DE ENERGIA - ADILSON' => '#c6ff00','GEO - FRANCIANO' => '#64dd17','ER/ AP - CLEU/ RAIANE' => '#9ccc65','D TECNICO LUCIMERY' => '#ab47bc', 
    'CIÊNCIAS - JOÃO' => '#ef5350','PV - CAROLINE' => '#9575cd','LI - MÔNICA' => '#7986cb','E.O RAIMARA' => '#4dd0e1','PROJ DE VIDA - TIAO' => '#7e57c2','FIS M ENERGE - JERRY' => '#81c784','P EXP MAT- DELTIANE' => '#26a69a','MAT SOCIEDADE- DELTIANE' => '#ec407a','E.O WALAS' => '#673ab7', 
    'ARTE - LUCIMERY' => '#ffb74d','P CIENT - JERRY' => '#43a047','PROJ VIDA - RAIANE' => '#64b5f6','E.O CLEU' => '#546e7a',' P.EXP - WALAS' => '#5c6bc0','L.P.A WEB - LUAN' => '#42a5f5','C DIG - PETERSON' => '#29b6f6','L P ORIENT - PETERSON' => '#26c6da','P EXP CIEN NAT JERRY' => '#1e88e5', 
    'E O - CAROLINE' => '#ffa726','AP - CLEU' => '#90a4ae','E FIS - LETÍCIA' => '#4fc3f7','CIENCIAS - ADILSON' => '#aed581','A L PROG - LUAN' => '#26a69a','APP WEB - LUAN' => '#00acc1','IOT - DEVILSON' => '#009688','D. GAMES - PETERSON' => '#26c6da','P EXP CIEN ADILSON' => '#f06292', 
    'P EXP - JUSSARA' => '#ec407a','P CLUBE - MÔNICA' => '#ab47bc','BIO - JUSSARA' => '#4caf50','HIS - CARMINHA' => '#8e24aa','EO - GERALDO' => '#66bb6a','H S SEG - JERRY' => '#039be5','ARQ S P R - DEVILSON' => '#00acc1','S OP - HEITOR' => '#455a64','PV - CAROLAINE' => '#f06292', 
    'FÍSICA - JERRY' => '#26a69a','QUÍMICA - KAMILA' => '#e53935','INGLÊS - GISLENE' => '#ff7043','P CLUBE - GISLENE' => '#00bfa5','P W DES - LUAN' => '#42a5f5','A P SIST - PETERSON' => '#00897b','B. DADOS - HEITOR' => '#5c6bc0','E.O RAIMARA' => '#7e57c2','ARTE CAROLAINE' => '#7e57c2', 
    'SOC - RAIANE' => '#ffa000','FILOSOFIA - TIÃO' => '#9575cd','MAT ENERGIA - KAMILA' => '#ff8f00','PE CIENCIAS - JOAO'  => '#9ccc65','MAT - WALAS'  => '#f44336','P EXP - PETERSON' => '#00acc1','D SISTEMAS - HEITOR' => '#5e35b1','BIO - ADILSON' => '#66bb6a','E.O JAQUELINE' => '#7986cb', 
    'LP - MARIA DULCE' => '#8e24aa','APP WEB - LUAN' => '#0097a7','ELETIVA' => '#536dfe','CIENCIAS - JUSSARA'  => '#8bc34a','LP - RAIMARA'  => '#3949ab','LP - WILMEIKA' => '#a1887f','MAT - DELTIANE' => '#7e57c2','P EMP - JOSÉ V' => '#6d4c41','P CIENTÍFICO - KAMILA' => '#ba68c8',
];

// Buscar horários existentes
$stmt = $pdo->query("SELECT * FROM horarios");
$horarios = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
  $horarios[$row['dia']][$row['turma']][$row['aula']] = $row['disciplina'];
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Editar Horários</title>
<style>
body{font-family:'Inter',sans-serif;background:#f2f6fa;margin:0;padding:0;}
header{background:#1a365d;color:#fff;padding:15px;text-align:center;}
table{border-collapse:collapse;width:100%;text-align:center;font-size:12px;}
th,td{border:1px solid #ccc;padding:6px;min-width:100px;}
th{background:#2b6fb3;color:#fff;}
.aula-col{background:#eee;font-weight:bold;}
.cell-select{font-weight:600;border-radius:6px;padding:5px;width:100%;cursor:pointer;}
.btn-save{display:block;margin:20px auto;background:#1a365d;color:#fff;padding:10px 20px;border:none;border-radius:8px;font-size:15px;cursor:pointer;}
.btn-save:hover{background:#2b6fb3;}
.modal-pesquisa{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999;}
.modal-content{background:#fff;padding:20px;border-radius:12px;max-width:500px;width:90%;box-shadow:0 8px 20px rgba(0,0,0,0.2);}
.modal-content h2{text-align:center;margin-bottom:10px;}
#busca{width:100%;padding:10px;margin-bottom:10px;border:1px solid #ccc;border-radius:8px;}
.lista-disc{max-height:250px;overflow-y:auto;border:1px solid #ddd;border-radius:8px;}
.lista-disc div{padding:8px;margin:4px;border-radius:6px;cursor:pointer;color:#fff;}
.lista-disc div:hover{opacity:0.8;}
.btn-add,.btn-del{margin-top:10px;display:inline-block;background:#2b6fb3;color:#fff;padding:8px 14px;border:none;border-radius:6px;cursor:pointer;}
.btn-del{background:#d32f2f;margin-left:10px;}
.btn-add:hover{background:#1a365d;}
.btn-del:hover{background:#b71c1c;}
</style>
</head>
<body>

<header style="position: relative; background:#1a365d; color:white; padding:15px; text-align:center;">
    <h1 style="margin:0;">Editar Horários</h1>

    <nav style="position:absolute; right:20px; top:50%; transform:translateY(-50%);">
        <a href="pagina_diretor.php" style="color:white; font-weight:bold; text-decoration:none;">Home</a>
    </nav>
</header>



<div class="container">
<form method="post" action="salvar_horarios.php">
<?php foreach($dias as $dia): ?>
<h3 style="text-align:center;color:#1a365d;margin-top:20px"><?php echo $dia; ?></h3>
<table>
<thead>
<tr>
<th>Aula</th>
<?php foreach($turmas as $turma): ?><th><?php echo $turma; ?></th><?php endforeach; ?>
</tr>
</thead>
<tbody>
<?php foreach($aulas as $aula): ?>
<tr>
<td class="aula-col"><?php echo $aula; ?></td>
<?php foreach($turmas as $turma):
$valor = $horarios[$dia][$turma][$aula] ?? '';
$cor = $cores[$valor] ?? '#888';
?>
<td>
  <select class="cell-select" data-dia="<?php echo $dia ?>" data-turma="<?php echo $turma ?>" data-aula="<?php echo $aula ?>" style="background:<?php echo $cor ?>; color:#fff">
    <option value="">--</option>
    <?php foreach($cores as $disc => $c): ?>
    <option value="<?php echo $disc ?>" <?php if($disc==$valor) echo 'selected'; ?> style="background:<?php echo $c ?>; color:#fff"><?php echo $disc ?></option>
    <?php endforeach; ?>
  </select>
  <input type="hidden" name="horarios[<?php echo $dia ?>][<?php echo $turma ?>][<?php echo $aula ?>]" value="<?php echo $valor ?>">
</td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endforeach; ?>
<button type="submit" class="btn-save">💾 Salvar Alterações</button>
</form>
</div>

<script>
let cores = <?php echo json_encode($cores); ?>;
const selects = document.querySelectorAll('.cell-select');

// Atualiza cor ao mudar
selects.forEach(sel=>{
  sel.addEventListener('change',()=>{
      const novo = sel.value;
      sel.style.background = cores[novo] || '#888';
      sel.style.color = '#fff';
      const hidden = sel.parentElement.querySelector('input[type="hidden"]');
      if(hidden) hidden.value = novo;
  });

  // Ao focar, abrir pesquisa
  sel.addEventListener('focus', ()=>abrirPesquisa(sel));
});

function abrirPesquisa(sel){
  const modalExistente = document.querySelector('.modal-pesquisa');
  if(modalExistente) modalExistente.remove(); // 🔧 evita duplicação

  const modal = document.createElement('div');
  modal.className = 'modal-pesquisa';
  modal.innerHTML = `
    <div class="modal-content">
      <h2>Selecionar Disciplina</h2>
      <input type="text" id="busca" placeholder="🔍 Pesquisar disciplina...">
      <div class="lista-disc"></div>
      <button class="btn-add">➕ Adicionar nova</button>
      <button class="btn-del">❌ Excluir</button>
    </div>
  `;
  document.body.appendChild(modal);

  const lista = modal.querySelector('.lista-disc');
  const input = modal.querySelector('#busca');
  atualizarLista('');

  function atualizarLista(filtro){
    lista.innerHTML = '';
    Object.keys(cores).forEach(d=>{
      if(d.toLowerCase().includes(filtro.toLowerCase())){
        const div = document.createElement('div');
        div.textContent = d;
        div.style.background = cores[d];
        div.addEventListener('click', ()=>{
          sel.value = d;
          sel.style.background = cores[d];
          const hidden = sel.parentElement.querySelector('input[type="hidden"]');
          hidden.value = d;
          modal.remove();
        });
        lista.appendChild(div);
      }
    });
  }

  input.addEventListener('input',()=>atualizarLista(input.value));

  modal.querySelector('.btn-add').addEventListener('click',()=>{
    const nome = prompt("Digite o nome da nova disciplina:");
    if(!nome) return;
    const cor = prompt("Digite a cor em HEX (ex: #ff0000):", "#"+Math.floor(Math.random()*16777215).toString(16));
    if(!cor.startsWith("#")) return alert("Cor inválida!");
    cores[nome] = cor;
    atualizarLista(input.value);
    alert("Disciplina adicionada!");
  });

  modal.querySelector('.btn-del').addEventListener('click',()=>{
    const nome = prompt("Digite o nome exato da disciplina para excluir:");
    if(cores[nome]){
      delete cores[nome];
      atualizarLista(input.value);
      alert("Disciplina removida!");
    } else {
      alert("Disciplina não encontrada!");
    }
  });

  modal.addEventListener('click',e=>{
    if(e.target === modal) modal.remove();
  });
}
</script>
</body>
</html>
