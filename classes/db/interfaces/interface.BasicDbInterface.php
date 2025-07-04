<?php
declare(strict_types=1);
/**
 *  This file is part of the AI Pic Repository Page Component plugin for ILIAS, which allows
 *  users of your platform to add an image component to their pages, generated by an external AI model.
 *  This plugin is created and maintained by SURLABS.
 *
 *  The AI Pic Repository Page Component plugin for ILIAS is open-source and licensed under GPL-3.0.
 *  For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 *  To report bugs or participate in discussions, visit the Mantis system and filter by
 *  the category "AI Pic" at https://mantis.ilias.de.
 *
 *  More information and source code are available at:
 *  https://github.com/surlabs/AIPicForILIAS
 *
 *  If you need support, please contact the maintainer of this software at:
 *  info@surlabs.com
 *
 */

namespace interfaces;

use Exception;

/**
 * interface BaseDbInterface
 * @authors Sergio Santiago, Abraham Morales <info@surlabs.com>
 */

interface BaseDbInterface {

    /**
     * @throws Exception
     */
    public function select(string $table, ?array $where = null, ?array $columns = null, ?string $extra = ""): array;

    /**
     * @throws Exception
     */
    public function delete(string $table, array $where): void;

    /**
     * @throws Exception
     */
    public function update(string $table, array $data, array $where): void;

    /**
     * @throws Exception
     */
    public function insert(string $table, array $data): void;

    /**
     * @throws Exception
     */
    public function insertOnDuplicatedKey(string $table, array $data): void;

    /**
     * @throws Exception
     */
    public function nextId(string $table): int;

    /**
     * @throws Exception
     */
    public function validateTableNameOrThrow(string $identifier): void;

    /**
     * @throws Exception
     */
    public function throwInvalidTableNameException(string $identifier): void;


}