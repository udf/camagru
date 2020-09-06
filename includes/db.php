<?php
require_once(__DIR__ . '/../config/database.php');

class DB {
    protected $conn;

    function __construct($use_database = true) {
        global $DB_NAME, $DB_DSN, $DB_USER, $DB_PASSWORD;

        try {
            $this->conn = new PDO($DB_DSN, $DB_USER, $DB_PASSWORD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die('Database connection error: ' . $e->getMessage());
        }

        if ($use_database) {
            try {
                $this->conn->query("USE camagru;");
            } catch(PDOException $error) {
                die('Failed to find database, please run config/setup.php first!');
            }
        }
    }

    function setup() {
        $this->conn->query('DROP DATABASE IF EXISTS camagru');
        $this->conn->query('CREATE DATABASE camagru; USE camagru;');

        $this->conn->query('CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(256) UNIQUE NOT NULL,
            email VARCHAR(256) UNIQUE NOT NULL,
            pw_hash VARCHAR(60) NOT NULL,
            verify_id VARCHAR(64) DEFAULT NULL,
            pw_change_id VARCHAR(64) DEFAULT NULL,
            email_notifications BOOLEAN DEFAULT 1
        );');

        $this->conn->query('CREATE TABLE images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            filename VARCHAR(256) NOT NULL,
            date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            like_count INT NOT NULL DEFAULT 0,
            comment_count INT NOT NULL DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id),
            UNIQUE INDEX (user_id, filename)
        );');

        $this->conn->query('CREATE TABLE comments (
            image_id INT NOT NULL,
            user_id INT NOT NULL,
            text TEXT NOT NULL,
            date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (image_id) REFERENCES images(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        );');

        $this->conn->query('CREATE TRIGGER COMMENT_INCREMENT
            AFTER INSERT ON comments
            FOR EACH ROW
                UPDATE images SET comment_count = comment_count + 1
                WHERE id = NEW.image_id;
        ');

        $this->conn->query('CREATE TABLE likes (
            image_id INT NOT NULL,
            user_id INT NOT NULL,
            FOREIGN KEY (image_id) REFERENCES images(id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            UNIQUE INDEX (image_id, user_id)
        );');

        $this->conn->query('CREATE TRIGGER LIKE_INCREMENT
            AFTER INSERT ON likes
            FOR EACH ROW
                UPDATE images SET like_count = like_count + 1
                WHERE id = NEW.image_id;
        ');

        $this->conn->query('CREATE TRIGGER LIKE_DECREMENT
            AFTER DELETE ON likes
            FOR EACH ROW
                UPDATE images SET like_count = like_count - 1
                WHERE id = OLD.image_id;
        ');

        $this->conn->query('CREATE FUNCTION ToggleLike
            (
                _image_id INT,
                _user_id INT
            )
            RETURNS INT
            BEGIN
                DELETE FROM likes WHERE user_id = _user_id AND image_id = _image_id;

                IF (ROW_COUNT() > 0) THEN
                    RETURN 0;
                END IF;
                INSERT INTO likes (image_id, user_id) VALUES (_image_id, _user_id);
                RETURN 1;
            END
        ');

        echo "Successfully (re)created database!";
    }

    function add_user($username, $email, $password) {
        $sql = $this->conn->prepare('
            INSERT INTO users
                (`username`, `email`, `pw_hash`, `verify_id`)
            VALUES
                (?, ?, ?, ?);
        ');
        $pw_hash = password_hash($password, PASSWORD_BCRYPT);
        $verify_id = bin2hex(random_bytes(32));

        try {
            $sql->execute([$username, $email, $pw_hash, $verify_id]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000')
                throw new RuntimeException('A user with those details already exists');
        }
        return $verify_id;
    }

    function get_user($username_or_email) {
        $sql = $this->conn->prepare('
            SELECT
                *
            FROM `users` WHERE
                (email = ? OR username = ?);
        ');

        try {
            $sql->execute([$username_or_email, $username_or_email]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result === false)
            throw new RuntimeException('A user with those details was not found!');
        return $result;
    }

    function verify_user($verify_id) {
        $sql = $this->conn->prepare('
            UPDATE users SET
                verify_id = NULL
            WHERE
                verify_id = ?;
        ');

        try {
            $sql->execute([$verify_id]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
    }

    function update_user($id, $username, $email_notifications) {
        $sql = $this->conn->prepare('
            UPDATE users SET
                username = ?,
                email_notifications = ?
            WHERE
                id = ?;
        ');

        try {
            $sql->execute([$username, $email_notifications, $id]);
        } catch (PDOException $e) {
            throw new RuntimeException('The username that you entered is already in use!');
        }
    }

    function update_email($id, $email) {
        $sql = $this->conn->prepare('
            UPDATE users SET
                email = ?,
                verify_id = ?
            WHERE
                id = ?;
        ');
        $verify_id = bin2hex(random_bytes(32));

        try {
            $sql->execute([$email, $verify_id, $id]);
        } catch (PDOException $e) {
            throw new RuntimeException('The email that you entered is already in use!');
        }
    }

    function update_password($id, $password) {
        $sql = $this->conn->prepare('
            UPDATE users SET
                pw_hash = ?,
                pw_change_id = NULL
            WHERE
                id = ?;
        ');
        $pw_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $sql->execute([$pw_hash, $id]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
        return $pw_hash;
    }

    function init_pw_reset($id) {
        $sql = $this->conn->prepare('
            UPDATE users SET
                pw_change_id = ?
            WHERE
                id = ?;
        ');
        $pw_change_id = bin2hex(random_bytes(32));

        try {
            $sql->execute([$pw_change_id, $id]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
        return $pw_change_id;
    }

    function get_user_by_pw_change_id($pw_change_id) {
        $sql = $this->conn->prepare('
            SELECT
                *
            FROM `users` WHERE
                pw_change_id = ?;
        ');

        try {
            $sql->execute([$pw_change_id]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result === false)
            throw new RuntimeException('Invalid token');
        return $result;
    }

    function add_image($user_id, $filename) {
        $sql = $this->conn->prepare('
            INSERT INTO images
                (`user_id`, `filename`)
            VALUES
                (?, ?);
        ');

        try {
            $sql->execute([$user_id, $filename]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000')
                throw new RuntimeException('You have posted this image before');
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
    }

    function get_images($start, $user_id) {
        global $PAGE_SIZE;

        $sql = $this->conn->prepare(
            'SELECT
                a.id,
                a.filename,
                a.like_count,
                a.comment_count,
                a.date,
                b.username as username,
                (SELECT EXISTS (SELECT 1 FROM likes WHERE image_id = a.id AND user_id = :user_id)) as is_liked
            FROM images as a
            JOIN users as b ON a.user_id = b.id
            ORDER BY a.date DESC
            LIMIT :start, :page_size;'
        );

        try {
            $sql->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $sql->bindValue(':start', $start, PDO::PARAM_INT);
            $sql->bindValue(':page_size', $PAGE_SIZE, PDO::PARAM_INT);
            $sql->execute();
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    function get_image($image_id, $user_id) {
        $sql = $this->conn->prepare(
            'SELECT
                a.id,
                a.filename,
                a.like_count,
                a.comment_count,
                a.date,
                b.username as username,
                (SELECT EXISTS (SELECT 1 FROM likes WHERE image_id = a.id AND user_id = :user_id)) as is_liked
            FROM images as a
            JOIN users as b ON a.user_id = b.id
            WHERE a.id = :image_id
            ;'
        );

        try {
            $sql->bindValue(':image_id', $image_id, PDO::PARAM_INT);
            $sql->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $sql->execute();
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    function count_images() {
        $sql = $this->conn->prepare('
            SELECT
                COUNT(*) as count
            FROM images;
        ');

        try {
            $sql->execute();
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result === false)
            throw new RuntimeException('Sorry, an unexpected error occured.');
        return $result['count'];
    }

    function toggle_like($image_id, $user_id) {
        $sql = $this->conn->prepare('
            SELECT ToggleLike(:image_id, :user_id) as isLiked;
        ');

        try {
            $sql->bindValue(':image_id', $image_id, PDO::PARAM_INT);
            $sql->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $sql->execute();
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
        $result = $sql->fetch(PDO::FETCH_ASSOC);
        if ($result === false)
            throw new RuntimeException('Sorry, an unexpected error occured.');
        return $result;
    }

    function add_comment($image_id, $user_id, $text) {
        $sql = $this->conn->prepare('
            INSERT INTO comments
                (image_id, user_id, text)
            VALUES
                (?, ?, ?);
        ');

        try {
            $sql->execute([$image_id, $user_id, $text]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
    }

    function get_comments($image_id) {
        $sql = $this->conn->prepare('
            SELECT
                b.username as username, a.text, a.date
            FROM
                comments as a
            JOIN
                users as b ON a.user_id = b.id
            WHERE
                image_id = ?
            ORDER BY
                a.date DESC;
        ');

        try {
            $sql->execute([$image_id]);
        } catch (PDOException $e) {
            throw new RuntimeException('Sorry, an unexpected error occured.');
        }
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

}
?>
