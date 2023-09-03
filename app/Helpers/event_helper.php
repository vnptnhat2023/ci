<?php
/**
 * R2h events.
 */
class r2hEvents
{
    public function R2hBeforeLogin ( array $data ) : array
    {
        echo '<p>Before Login</p>';
        return $data;
    }

    public function R2hAfterLogin ( array $data ) : array
    {
        echo '<p>After Login</p>';
        return $data;
    }

    public function R2hBeforeLogout ( array $data ) : array
    {
        echo '<p>Before Logout</p>';
        return $data;
    }

    public function R2hAfterLogout ( array $data ) : array
    {
        echo '<p>After Logout</p>';
        return $data;
    }
}