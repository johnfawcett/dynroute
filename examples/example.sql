; Copyright (c) 2009-2022 John Fawcett

; Examples to create database and table for 
; testing dynamic routing


CREATE DATABASE `customer_routing`;

USE `customer_routing`;


CREATE TABLE `customer_routing` (
  `customer_routing_id` int(11) NOT NULL AUTO_INCREMENT,
  `callerid` varchar(25) NOT NULL,
  `result` varchar(25) NOT NULL,
  PRIMARY KEY (`customer_routing_id`),
  KEY `callerid` (`callerid`)
) DEFAULT CHARSET=utf8;



