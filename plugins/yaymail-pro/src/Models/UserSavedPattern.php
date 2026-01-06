<?php

namespace YayMail\Models;

use YayMail\Utils\SingletonTrait;

/**
 * UserSavedPattern Model
 *
 * @method static UserSavedPattern get_instance()
 */
class UserSavedPattern {
    use SingletonTrait;

    const OPTION_NAME = 'yaymail_saved_patterns';

    public static function find_all() {
        $default  = [];
        $patterns = get_option( self::OPTION_NAME, [] );
        if ( ! is_array( $patterns ) ) {
            return $default;
        }
        return $patterns;
    }

    /**
     * Saves a new pattern to the list of saved patterns.
     *
     * @param array $new_pattern The new pattern to save.
     * @return array The updated list of patterns after adding the new pattern.
     * @throws \Exception If the pattern is not successfully saved.
     */
    public function save( $new_pattern ) {
        $patterns_from_db          = get_option( self::OPTION_NAME ) ?? [];
        $new_pattern['id']         = uniqid();
        $new_pattern['created_at'] = current_time( 'mysql' );

        $patterns_from_db[] = $new_pattern;

        $update_result = update_option( self::OPTION_NAME, $patterns_from_db );
        if ( $update_result ) {

            return $patterns_from_db;
        }
        throw new \Exception( 'Failed to save the pattern.' );

    }

    /**
     * Deletes a pattern from the saved patterns list based on its ID.
     *
     * @param string $pattern_id The ID of the pattern to delete.
     * @return array The updated list of patterns after deletion.
     * @throws \Exception If the pattern is not found or if the update fails.
     */
    public function delete( $pattern_id ) {
        $patterns_from_db = get_option( self::OPTION_NAME, [] );

        // Find the index of the pattern with the specified ID
        $index = array_search( $pattern_id, array_column( $patterns_from_db, 'id' ), true );

        // If the pattern is found, remove it from the array
        if ( false !== $index ) {
            unset( $patterns_from_db[ $index ] );

            // Reindex the array to ensure sequential numeric keys
            $patterns_from_db = array_values( $patterns_from_db );

            // Update the option with the modified array
            $update_result = update_option( self::OPTION_NAME, $patterns_from_db );
            if ( $update_result ) {
                return $patterns_from_db;
            }
            throw new \Exception( 'Failed to update the pattern.' );
        }
        throw new \Exception( 'Pattern not found' );

    }
}
