/* To get started run the following SQL commands.  */

CREATE DATABASE profiles;

-- (Optional)
-- Create a MySQL user manually with full access to this database.
-- Example:
-- CREATE USER 'youruser'@'localhost' IDENTIFIED BY 'yourpassword';
-- GRANT ALL PRIVILEGES ON profiles.* TO 'youruser'@'localhost';

USE profiles; -- Or select profiles in phpMyAdmin

-- Create the table users and populate with some data
CREATE TABLE users (
   user_id INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,
   name VARCHAR(128),
   email VARCHAR(128),
   hashed_password VARCHAR(128),
   INDEX(email)
) ENGINE=InnoDB CHARSET=utf8;

/*
Password hashing in this application uses a fixed salt value:
    $salt = 'XyZzy12*_';
The stored password hash is generated using:
    md5($salt . $password)
Example: To generate a hash for the password "456" in PHP:
    echo hash('md5', 'XyZzy12*_' . '456');
This will produce the value: e7cf3ef4f17c3999a94f2c6f612e8a888

 Example users to test login: 
 1) Email: alex@email.com
    Password: 123
    Stored hash = md5("XyZzy12*_" . "123") → 1a52e17fa899cf40fb04cfc42e6352f1    
 2) Email: alexia@email.com
    Password: 456
    Stored hash = md5("XyZzy12*_" . "456") → e7cf3ef4f17c3999a94f2c6f612e8a88
*/

INSERT INTO users (name, email, hashed_password)
VALUES ('Alex', 'alex@email.com', '1a52e17fa899cf40fb04cfc42e6352f1');

INSERT INTO users (name, email, hashed_password)
VALUES ('Alexia', 'alexia@email.com', 'e7cf3ef4f17c3999a94f2c6f612e8a88');

-- Create the table profile. Can be populated from the code
CREATE TABLE profile (
  profile_id INTEGER NOT NULL AUTO_INCREMENT,
  user_id INTEGER NOT NULL,
  first_name TEXT,
  last_name TEXT,
  email TEXT,
  headline TEXT,
  summary TEXT,
  PRIMARY KEY(profile_id),
  CONSTRAINT profile_ibfk_2
  FOREIGN KEY (user_id)
  REFERENCES users (user_id)
  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create the table positions which has a many-to-one relationship with the table profile.
-- Each profile can have multiple positions. The table can be populated from the code
CREATE TABLE position (
  position_id INTEGER NOT NULL AUTO_INCREMENT,
  profile_id INTEGER,
  rank INTEGER,
  year INTEGER,
  description TEXT,
  PRIMARY KEY(position_id),
  CONSTRAINT position_ibfk_1
    FOREIGN KEY (profile_id)
    REFERENCES profile (profile_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create the institution table to store unique educational institutions.
CREATE TABLE institution (
  institution_id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255),
  UNIQUE(name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Create the Education table which implements a many-to-many relationship
-- between Profile and Institution. Each profile can be associated with multiple
-- institutions and each institution can be associated with multiple profiles.
CREATE TABLE education (
  profile_id INTEGER,
  institution_id INTEGER,
  rank INTEGER,
  year INTEGER,
  CONSTRAINT education_ibfk_1
    FOREIGN KEY (profile_id)
    REFERENCES Profile (profile_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT education_ibfk_2
    FOREIGN KEY (institution_id)
    REFERENCES institution (institution_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY(profile_id, institution_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Pre-insert some university data into the institution table so it can be referenced by the education table.
INSERT INTO Institution (name) VALUES ('National Technical University of Athens');
INSERT INTO Institution (name) VALUES ('National and Kapodistrian University of Athens');
INSERT INTO Institution (name) VALUES ('Aristotle University of Thessaloniki');
INSERT INTO Institution (name) VALUES ('Athens University of Economics and Business');
INSERT INTO Institution (name) VALUES ('University of Patras');
INSERT INTO Institution (name) VALUES ('University of Crete');
INSERT INTO Institution (name) VALUES ('University of Ioannina');
INSERT INTO Institution (name) VALUES ('University of Macedonia');
INSERT INTO Institution (name) VALUES ('Technical University of Crete');
