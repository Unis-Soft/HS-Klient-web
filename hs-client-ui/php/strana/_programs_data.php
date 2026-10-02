<?php
/*
 * HairSoft Klient V241 - read-only helper for HSBridge PROGRAMS mirror tables and delivered photo totals.
 *
 * The HSBridge endpoint is the only writer. This file only reads the mirror
 * tables and maps them to the current HS Klient branch/customer.
 */

if (!function_exists('hsProgramsGroupId')) {
    // V236: HairSoft program names can pass through legacy Windows-1250/Latin-1
    // conversions. Canonicalise the known built-in label by a folded key so
    // Ě, Ì, Í and their title-case variants all resolve to one safe value.
    function hsProgramsDisplayName(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);
        if ($value === '') {
            return '';
        }

        $folded = strtr($value, array(
            'Ã“' => 'O', 'Ã³' => 'o', 'Äš' => 'E', 'Ä›' => 'e',
            'Á' => 'A', 'á' => 'a', 'Č' => 'C', 'č' => 'c',
            'Ď' => 'D', 'ď' => 'd', 'É' => 'E', 'é' => 'e',
            'Ě' => 'E', 'ě' => 'e', 'Í' => 'I', 'í' => 'i',
            'Ì' => 'I', 'ì' => 'i', 'Ň' => 'N', 'ň' => 'n',
            'Ó' => 'O', 'ó' => 'o', 'Ř' => 'R', 'ř' => 'r',
            'Š' => 'S', 'š' => 's', 'Ť' => 'T', 'ť' => 't',
            'Ú' => 'U', 'ú' => 'u', 'Ů' => 'U', 'ů' => 'u',
            'Ý' => 'Y', 'ý' => 'y', 'Ž' => 'Z', 'ž' => 'z',
        ));
        $folded = strtoupper($folded);
        $folded = preg_replace('/[^A-Z0-9]+/', ' ', $folded) ?? $folded;
        $folded = trim(preg_replace('/\s+/', ' ', $folded) ?? $folded);

        if ($folded === 'ZONY TELA' || $folded === 'ZONY TILA') {
            return 'Zóny těla';
        }

        return $value;
    }


    function hsProgramsGroupId(mysqli $mysqli, int $swId): int
    {
        if ($swId <= 0) {
            return 0;
        }

        $stmt = $mysqli->prepare('SELECT COALESCE(sw_skupina_id,0) AS group_id FROM sw_info WHERE sw_id=? LIMIT 1');
        if (!$stmt) {
            error_log('HairSoft Programs: group prepare failed: ' . $mysqli->error);
            return 0;
        }
        $stmt->bind_param('i', $swId);
        if (!$stmt->execute()) {
            error_log('HairSoft Programs: group execute failed: ' . $stmt->error);
            $stmt->close();
            return 0;
        }
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        $stmt->close();
        return $row && isset($row['group_id']) ? (int) $row['group_id'] : 0;
    }

    function hsProgramsSafeRows(mysqli $mysqli, string $sql): array
    {
        $result = $mysqli->query($sql);
        if (!$result) {
            error_log('HairSoft Programs query failed: ' . $mysqli->error . ' | SQL=' . $sql);
            return array();
        }

        $rows = array();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        return $rows;
    }

    function hsProgramsTableExists(mysqli $mysqli, string $table): bool
    {
        if ($table === '' || !preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            return false;
        }
        $escaped = $mysqli->real_escape_string($table);
        $result = $mysqli->query("SHOW TABLES LIKE '" . $escaped . "'");
        if (!$result) {
            return false;
        }
        $exists = $result->num_rows > 0;
        $result->free();
        return $exists;
    }

    function hsProgramsActiveMeta(mysqli $mysqli, int $swId, int $groupId, int $requestedProgramId = 0): array
    {
        $meta = array(
            'activePrograms' => array(),
            'selectedProgramId' => '',
            'totalRemaining' => 0,
        );
        if ($swId <= 0 || $groupId <= 0) {
            return $meta;
        }

        $sql = 'SELECT p.program_hs_id, p.name, SUM(active.remaining) AS total_remaining '
            . 'FROM hsbridge_programs p '
            . 'JOIN ('
            . '  SELECT z.program_hs_id, z.customer_hs_id, SUM(z.delta_visits) AS remaining '
            . '  FROM ('
            . '    SELECT program_hs_id, customer_hs_id, visits AS delta_visits '
            . '    FROM hsbridge_program_payments '
            . '    WHERE sw_id=' . $swId . ' AND group_id=' . $groupId . ' '
            . '    UNION ALL '
            . '    SELECT program_hs_id, customer_hs_id, -quantity AS delta_visits '
            . '    FROM hsbridge_program_visits '
            . '    WHERE sw_id=' . $swId . ' AND group_id=' . $groupId . ' '
            . '  ) z '
            . '  GROUP BY z.program_hs_id, z.customer_hs_id '
            . ') active ON active.program_hs_id=p.program_hs_id '
            . 'WHERE p.sw_id=' . $swId . ' AND p.group_id=' . $groupId . ' '
            . 'GROUP BY p.program_hs_id, p.name '
            . 'ORDER BY p.program_hs_id ASC';

        $rows = hsProgramsSafeRows($mysqli, $sql);
        $selected = 0;
        foreach ($rows as $row) {
            $id = isset($row['program_hs_id']) ? (int) $row['program_hs_id'] : 0;
            $name = isset($row['name']) ? hsProgramsDisplayName((string) $row['name']) : '';
            if ($id <= 0 || $name === '') {
                continue;
            }
            $remaining = isset($row['total_remaining']) ? (int) $row['total_remaining'] : 0;
            $meta['activePrograms'][] = array(
                'id' => (string) $id,
                'name' => $name,
                'remaining' => $remaining,
            );
            $meta['totalRemaining'] += $remaining;
            if ($id === $requestedProgramId) {
                $selected = $id;
            }
        }

        if ($selected <= 0 && !empty($meta['activePrograms'])) {
            $selected = (int) $meta['activePrograms'][0]['id'];
        }
        if ($selected > 0) {
            $meta['selectedProgramId'] = (string) $selected;
        }

        return $meta;
    }

    function hsProgramsBalancesForCustomers(mysqli $mysqli, int $swId, int $groupId, int $programId, array $customerIds): array
    {
        if ($swId <= 0 || $groupId <= 0 || $programId <= 0 || empty($customerIds)) {
            return array();
        }

        $ids = array();
        foreach ($customerIds as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (empty($ids)) {
            return array();
        }

        $idSql = implode(',', array_values($ids));
        $sql = 'SELECT z.customer_hs_id, SUM(z.delta_visits) AS remaining '
            . 'FROM ('
            . '  SELECT customer_hs_id, visits AS delta_visits '
            . '  FROM hsbridge_program_payments '
            . '  WHERE sw_id=' . $swId . ' AND group_id=' . $groupId . ' AND program_hs_id=' . $programId
            . '    AND customer_hs_id IN (' . $idSql . ') '
            . '  UNION ALL '
            . '  SELECT customer_hs_id, -quantity AS delta_visits '
            . '  FROM hsbridge_program_visits '
            . '  WHERE sw_id=' . $swId . ' AND group_id=' . $groupId . ' AND program_hs_id=' . $programId
            . '    AND customer_hs_id IN (' . $idSql . ') '
            . ') z '
            . 'GROUP BY z.customer_hs_id';

        $rows = hsProgramsSafeRows($mysqli, $sql);
        $balances = array();
        foreach ($rows as $row) {
            $customerId = isset($row['customer_hs_id']) ? (int) $row['customer_hs_id'] : 0;
            $remaining = isset($row['remaining']) ? (int) $row['remaining'] : 0;
            if ($customerId > 0) {
                $balances[$customerId] = $remaining;
            }
        }
        return $balances;
    }

    function hsProgramsCustomerDetail(mysqli $mysqli, int $swId, int $groupId, int $customerId, int $requestedProgramId = 0): array
    {
        $detail = array(
            'programs' => array(),
            'selectedProgramId' => 0,
        );
        if ($swId <= 0 || $groupId <= 0 || $customerId <= 0) {
            return $detail;
        }

        $programRows = hsProgramsSafeRows(
            $mysqli,
            'SELECT program_hs_id, name FROM hsbridge_programs '
            . 'WHERE sw_id=' . $swId . ' AND group_id=' . $groupId . ' ORDER BY program_hs_id ASC'
        );
        $programs = array();
        foreach ($programRows as $row) {
            $programId = isset($row['program_hs_id']) ? (int) $row['program_hs_id'] : 0;
            if ($programId <= 0) {
                continue;
            }
            $programs[$programId] = array(
                'id' => $programId,
                'name' => isset($row['name']) ? hsProgramsDisplayName((string) $row['name']) : '',
                'prepaid' => 0,
                'used' => 0,
                'remaining' => 0,
                'amount' => 0.0,
                'payments' => array(),
                'visits' => array(),
                'values' => array(),
                'photoCount' => 0,
                'lastActivity' => '',
                'hasData' => false,
            );
        }
        if (empty($programs)) {
            return $detail;
        }

        $paymentRows = hsProgramsSafeRows(
            $mysqli,
            'SELECT payment_hs_id, program_hs_id, price, price_vat, visits, created_at_hs '
            . 'FROM hsbridge_program_payments '
            . 'WHERE sw_id=' . $swId . ' AND group_id=' . $groupId . ' AND customer_hs_id=' . $customerId . ' '
            . 'ORDER BY created_at_hs DESC, payment_hs_id DESC'
        );
        foreach ($paymentRows as $row) {
            $programId = isset($row['program_hs_id']) ? (int) $row['program_hs_id'] : 0;
            if (!isset($programs[$programId])) {
                continue;
            }
            $visits = isset($row['visits']) ? (int) $row['visits'] : 0;
            $price = isset($row['price']) ? (float) $row['price'] : 0.0;
            $created = isset($row['created_at_hs']) ? (string) $row['created_at_hs'] : '';
            $programs[$programId]['prepaid'] += $visits;
            $programs[$programId]['amount'] += $price;
            $programs[$programId]['hasData'] = true;
            if ($created !== '' && ($programs[$programId]['lastActivity'] === '' || $created > $programs[$programId]['lastActivity'])) {
                $programs[$programId]['lastActivity'] = $created;
            }
            $programs[$programId]['payments'][] = array(
                'id' => isset($row['payment_hs_id']) ? (int) $row['payment_hs_id'] : 0,
                'price' => $price,
                'vat' => isset($row['price_vat']) ? (int) $row['price_vat'] : 0,
                'visits' => $visits,
                'created' => $created,
            );
        }

        $visitRows = hsProgramsSafeRows(
            $mysqli,
            'SELECT visit_hs_id, program_hs_id, visit_at_hs, quantity '
            . 'FROM hsbridge_program_visits '
            . 'WHERE sw_id=' . $swId . ' AND group_id=' . $groupId . ' AND customer_hs_id=' . $customerId . ' '
            . 'ORDER BY visit_at_hs DESC, visit_hs_id DESC'
        );
        foreach ($visitRows as $row) {
            $programId = isset($row['program_hs_id']) ? (int) $row['program_hs_id'] : 0;
            if (!isset($programs[$programId])) {
                continue;
            }
            $quantity = isset($row['quantity']) ? (int) $row['quantity'] : 0;
            $visit = isset($row['visit_at_hs']) ? (string) $row['visit_at_hs'] : '';
            $programs[$programId]['used'] += $quantity;
            $programs[$programId]['hasData'] = true;
            if ($visit !== '' && ($programs[$programId]['lastActivity'] === '' || $visit > $programs[$programId]['lastActivity'])) {
                $programs[$programId]['lastActivity'] = $visit;
            }
            $programs[$programId]['visits'][] = array(
                'id' => isset($row['visit_hs_id']) ? (int) $row['visit_hs_id'] : 0,
                'visit' => $visit,
                'quantity' => $quantity,
            );
        }

        $valueRows = hsProgramsSafeRows(
            $mysqli,
            'SELECT pv.program_hs_id, pv.program_value_hs_id, pv.name, cv.row_hs_id, cv.value_text '
            . 'FROM hsbridge_program_customer_values cv '
            . 'JOIN hsbridge_program_values pv '
            . '  ON pv.sw_id=cv.sw_id AND pv.group_id=cv.group_id AND pv.program_value_hs_id=cv.program_value_hs_id '
            . 'WHERE cv.sw_id=' . $swId . ' AND cv.group_id=' . $groupId . ' AND cv.customer_hs_id=' . $customerId . ' '
            . 'ORDER BY pv.program_hs_id ASC, pv.program_value_hs_id ASC, cv.row_hs_id ASC'
        );
        foreach ($valueRows as $row) {
            $programId = isset($row['program_hs_id']) ? (int) $row['program_hs_id'] : 0;
            if (!isset($programs[$programId])) {
                continue;
            }
            $programs[$programId]['hasData'] = true;
            $programs[$programId]['values'][] = array(
                'id' => isset($row['row_hs_id']) ? (int) $row['row_hs_id'] : 0,
                'definitionId' => isset($row['program_value_hs_id']) ? (int) $row['program_value_hs_id'] : 0,
                'name' => isset($row['name']) ? (string) $row['name'] : '',
                'value' => isset($row['value_text']) ? (string) $row['value_text'] : '',
            );
        }

        // V241: count only batches that HSBridge has actually confirmed as delivered.
        // The job row is intentionally kept after temporary files are deleted, so this
        // remains a durable audit/count without storing the photos in HS Klient.
        if (hsProgramsTableExists($mysqli, 'hsbridge_program_photo_jobs')) {
            $photoRows = hsProgramsSafeRows(
                $mysqli,
                'SELECT program_hs_id, COALESCE(SUM(total_files),0) AS photo_count '
                . 'FROM hsbridge_program_photo_jobs '
                . 'WHERE sw_id=' . $swId . ' AND group_id=' . $groupId . ' AND customer_hs_id=' . $customerId . " AND status='done' "
                . 'GROUP BY program_hs_id'
            );
            foreach ($photoRows as $photoRow) {
                $photoProgramId = isset($photoRow['program_hs_id']) ? (int) $photoRow['program_hs_id'] : 0;
                if ($photoProgramId > 0 && isset($programs[$photoProgramId])) {
                    $programs[$photoProgramId]['photoCount'] = isset($photoRow['photo_count']) ? max(0, (int) $photoRow['photo_count']) : 0;
                }
            }
        }

        foreach ($programs as $programId => &$program) {
            $program['remaining'] = (int) $program['prepaid'] - (int) $program['used'];
            if (!$program['hasData']) {
                unset($programs[$programId]);
            }
        }
        unset($program);

        if (empty($programs)) {
            return $detail;
        }

        $programList = array_values($programs);
        usort($programList, function (array $a, array $b): int {
            $lastA = isset($a['lastActivity']) ? (string) $a['lastActivity'] : '';
            $lastB = isset($b['lastActivity']) ? (string) $b['lastActivity'] : '';
            if ($lastA !== $lastB) {
                return strcmp($lastB, $lastA);
            }
            return strcasecmp((string) $a['name'], (string) $b['name']);
        });

        $selected = 0;
        foreach ($programList as $program) {
            if ((int) $program['id'] === $requestedProgramId) {
                $selected = $requestedProgramId;
                break;
            }
        }
        if ($selected <= 0) {
            $selected = (int) $programList[0]['id'];
        }

        $detail['programs'] = $programList;
        $detail['selectedProgramId'] = $selected;
        return $detail;
    }
}