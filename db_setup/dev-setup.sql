CREATE USER 'wikirumors'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL PRIVILEGES ON * . * TO 'wikirumors'@'localhost'; 
FLUSH PRIVILEGES;
