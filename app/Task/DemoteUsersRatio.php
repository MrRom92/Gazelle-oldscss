<?php

namespace Gazelle\Task;

use Gazelle\Enum\UserAuditEvent;

class DemoteUsersRatio extends \Gazelle\Task {
    public function run(): void {
        $userMan = new \Gazelle\Manager\User();
        foreach ($userMan->demotionCriteria() as $criteria) {
            $this->demote($criteria['To'], $criteria['Ratio'], $criteria['Upload'], $criteria['From'], $userMan);
        }
    }

    private function demote(int $newClass, float $ratio, int $upload, array $demoteClasses, \Gazelle\Manager\User $userMan): void {
        $userclassName = $userMan->userclassName($newClass);
        $placeholders = placeholders($demoteClasses);
        $query = self::$db->prepared_query("
            SELECT ID
            FROM users_main um
            INNER JOIN users_leech_stats AS uls ON (uls.UserID = um.ID)
            LEFT JOIN
            (
                SELECT rv.UserID, sum(Bounty) AS Bounty
                FROM requests_votes rv
                INNER JOIN requests r ON (r.ID = rv.RequestID)
                WHERE r.UserID != r.FillerID
                GROUP BY rv.UserID
            ) b ON (b.UserID = um.ID)
            WHERE (
                    (uls.Downloaded > 0 AND uls.Uploaded / uls.Downloaded < ?)
                    OR (uls.Uploaded + ifnull(b.Bounty, 0)) < ?
                )
                AND um.PermissionID IN ($placeholders)
            ", $ratio, $upload, ...$demoteClasses
        );

        self::$db->prepared_query("
            UPDATE users_info AS ui
            INNER JOIN users_main AS um ON (um.ID = ui.UserID)
            INNER JOIN users_leech_stats AS uls ON (uls.UserID = um.ID)
            LEFT JOIN
            (
                SELECT rv.UserID, sum(Bounty) AS Bounty
                FROM requests_votes rv
                INNER JOIN requests r ON (r.ID = rv.RequestID)
                WHERE r.UserID != r.FillerID
                GROUP BY rv.UserID
            ) b ON (b.UserID = um.ID)
            SET
                um.PermissionID = ?
            WHERE (
                    (uls.Downloaded > 0 AND uls.Uploaded / uls.Downloaded < ?)
                    OR (uls.Uploaded + ifnull(b.Bounty, 0)) < ?
                )
                AND um.PermissionID IN ($placeholders)
            ", $newClass, $ratio, $upload, ...$demoteClasses
        );

        self::$db->set_query_id($query);
        $demotions = 0;
        while ([$userId] = self::$db->next_record()) {
            $user = $userMan->findById($userId);
            if (is_null($user)) {
                continue;
            }
            $demotions++;
            $user->auditTrail()->addEvent(
                UserAuditEvent::ratio,
                "Demoted to $userclassName for insufficient ratio"
            );
            $user->flush();
            $user->inbox()->createSystem(
                "You have been demoted to $userclassName",
                "You now only meet the requirements for the \"$userclassName\" user class.\n\nTo read more about "
                    . SITE_NAME
                    . "'s user classes, read [url=wiki.php?action=article&name=userclasses]this wiki article[/url]."
            );
        }

        if ($demotions > 0) {
            $this->processed += $demotions;
            $this->info("Demoted $demotions users to $userclassName for insufficient ratio", $newClass);
        }
    }
}
