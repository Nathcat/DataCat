<?php
/**
 * Get the values in the row specified
 * @param p The puzzle
 * @param y The row index
 * @return array Containing all the values in the specified row
 */
function get_row($p, $y) {
    return $p[$y];
}

/**
 * Get the values in the column specified
 * @param p The puzzle
 * @param x The column index
 * @return array Containing all the values in the specified column
 */
function get_column($p, $x) {
    $c = [];
    for ($y = 0; $y < 9; $y++) {
        array_push($c, $p[$y][$x]);
    }

    return $c;
}

/**
 * Get the 3x3 box the specified space is contained in
 * @param p The puzzle
 * @param x The column of the space
 * @param y The row of the space
 * @return array Containing all the values in the 3x3 box
 */
function get_box($p, $x, $y) {
    $centre = [
        intdiv($x, 3) * 3 + 1,
        intdiv($y, 3) * 3 + 1
    ];
    
    $b = [];

    for ($r = $centre[1] - 1; $r <= $centre[1] + 1; $r++) {
        for ($c = $centre[0] - 1; $c <= $centre[0] + 1; $c++) {
            array_push($b, $p[$r][$c]);
        }
    }

    return $b;
}

/**
 * Check if $i is in $l, ignoring empty spaces.
 * @param i
 * @param l
 * @return bool true if i in l, false otherwise
 */
function check_values_ignore_arrays($i, $l) {
    for ($v = 0; $v < count($l); $v++) {
        if (!is_array($l[$v]) && $l[$v] === $i) {
            return false;
        }
    }

    return true;
}

/**
 * Get a list of candidates for the provided space
 * @param p The puzzle
 * @param x The column of the space
 * @param y The row of the space
 * @return array A list of candidates for the given space
 */
function get_candidates($p, $x, $y) {
    $row = get_row($p, $y);
    $column = get_column($p, $x);
    $box = get_box($p, $x, $y);

    $c = [];
    for ($i = 1; $i <= 9; $i++) {
        if (check_values_ignore_arrays($i, $row) && check_values_ignore_arrays($i, $column) && check_values_ignore_arrays($i, $box)) {
            array_push($c, $i);
        }
    }

    return $c;
}

/**
 * Find all the candidates of all empty spaces in the given
 * puzzle.
 * @param p The puzzle to search through
 * @return bool true if all spaces have candidates, false if
 *              any spaces have no candidates.
 */
function find_all_candidates($p) {
    $solvable = false;
    for ($y = 0; $y < 9; $y++) {
        for ($x = 0; $x < 9; $x++) {
            if (is_array($p[$y][$x]) || $p[$y][$x] === 0) {
                $p[$y][$x] = get_candidates($p, $x, $y);
                if (count($p[$y][$x]) == 0) {
                    $solvable = false;
                }
            }
            else {
                $v = $p[$y][$x];
                $p[$y][$x] = 0;
                $c = get_candidates($p, $x, $y);
                $p[$y][$x] = $v;
                if (in_array($v, $c, true)) return false;
            }
        }
    }

    return $solvable;
}

/**
 * Deep copy a puzzle array
 * @param arr The puzzle array to copy
 * @return array A copy of arr
 */
function deep_copy_array($arr) {
    $res = [];
    for ($i = 0; $i < count($arr); $i++) {
        if (is_array($arr[$i])) {
            array_push($res, deep_copy_array($arr[$i]));
        }
        else {
            array_push($res, $arr[$i]);
        }
    }

    return $res;
}

/**
 * Attempt to solve a Sudoku
 * @param p The puzzle to solve
 */
function solve($p) {
    for ($y = 0; $y < 9; $y++) {
        for ($x = 0; $x < 9; $x++) {
            if (is_array($p[$y][$x]) && count($p[$y][$x]) === 1) {
                $p[$y][$x] = $p[$y][$x][0];
            }
        }
    }

    if (!find_all_candidates($p)) {
        return false;
    }

    $candidates = NULL;
    $Y = 0;
    $X = 0;
    for ($y = 0; $y < 9; $y++) {
        if ($candidates == NULL) {
            for ($x = 0; $x < 9; $x++) {
                if (is_array($p[$y][$x])) {
                    $candidates = $p[$y][$x];
                    $X = $x;
                    $Y = $y;
                }
            }
        }
        else break;
    }

    if ($candidates == NULL) return $p;

    $p_copy = deep_copy_array($p);
    for ($i = 0; $i < count($candidates); $i++) {
        $p_copy[$Y][$X] = $candidates[$i];

        if (find_all_candidates($p_copy)) {
            $s = solve($p_copy);
            if ($s !== false) {
                return $s;
            }
        }
    }

    return false;
}

/**
 * Tests for empty spaces in the given puzzle
 * @param p The puzzle to test
 * @return bool true if empty spaces exist, false otherwise
 */
function test_empty_spaces($p) {
    for ($y = 0; $y < 9; $y++) {
        for ($x = 0; $x < 9; $x++) {
            if ($p[$y][$x] === 0) return true;
        }
    }

    return false;
}

/**
 * Test if the given puzzle is solved
 * @param p
 * @return bool true if solved, false otherwise
 */
function is_solved($p) {
    for ($y = 0; $y < 9; $y++) {
        for ($x = 0; $x < 9; $x++) {
            if ($p[$y][$x] === 0) return false;

            $v = $p[$y][$x];
            $p[$y][$x] = 0;
            $c = get_candidates($p, $x, $y);
            $p[$y][$x] = $v;

            if (check_values_ignore_arrays($v, $c)) return false;
        }
    }

    return true;
}
?>