<?php

namespace aeqdev\afw;

class Session
{

    protected static $start = 0;
    protected static $read = false;

    static function start()
    {
        if (!self::$start++) {
            session_start();
            self::$read = true;
        }
    }

    static function commit()
    {
        if (!--self::$start) {
            session_commit();
        }
    }

    static function read()
    {
        if (!self::$read) {
            if (isset($_COOKIE[session_name()])) {
                session_start();
                session_commit();
            }
            self::$read = true;
        }
    }

    static function get($name)
    {
        self::read();
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    static function set($name, $value)
    {
        self::start();
        $_SESSION[$name] = $value;
        self::commit();
    }

    static function destroy()
    {
        self::start();
        session_destroy();
    }

}
