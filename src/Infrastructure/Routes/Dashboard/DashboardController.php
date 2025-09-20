<?php

namespace MedX360\Infrastructure\Routes\Dashboard;

use MedX360\Infrastructure\Common\Container;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Dashboard Controller
 * 
 * @package MedX360\Infrastructure\Routes\Dashboard
 */
class DashboardController
{
    private $container;

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get dashboard statistics
     */
    public function getStats(WP_REST_Request $request)
    {
        try {
            global $wpdb;

            // Get total patients
            $totalPatients = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}healthcare_patients WHERE status = 'active'"
            );

            // Get active providers
            $activeProviders = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}healthcare_providers WHERE status = 'active'"
            );

            // Get today's appointments
            $todayAppointments = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}healthcare_appointments 
                     WHERE DATE(scheduled_at) = %s AND status IN ('scheduled', 'confirmed')",
                    date('Y-m-d')
                )
            );

            // Get pending appointments
            $pendingAppointments = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->prefix}healthcare_appointments WHERE status = 'pending'"
            );

            // Get this week's appointments
            $weekStart = date('Y-m-d', strtotime('monday this week'));
            $weekEnd = date('Y-m-d', strtotime('sunday this week'));
            
            $weekAppointments = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}healthcare_appointments 
                     WHERE DATE(scheduled_at) BETWEEN %s AND %s AND status IN ('scheduled', 'confirmed')",
                    $weekStart,
                    $weekEnd
                )
            );

            // Get this month's appointments
            $monthStart = date('Y-m-01');
            $monthEnd = date('Y-m-t');
            
            $monthAppointments = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}healthcare_appointments 
                     WHERE DATE(scheduled_at) BETWEEN %s AND %s AND status IN ('scheduled', 'confirmed')",
                    $monthStart,
                    $monthEnd
                )
            );

            // Get recent appointments
            $recentAppointments = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT a.*, p.first_name as patient_first_name, p.last_name as patient_last_name,
                            pr.first_name as provider_first_name, pr.last_name as provider_last_name
                     FROM {$wpdb->prefix}healthcare_appointments a
                     LEFT JOIN {$wpdb->prefix}healthcare_patients p ON a.patient_id = p.id
                     LEFT JOIN {$wpdb->prefix}healthcare_providers pr ON a.provider_id = pr.id
                     WHERE a.scheduled_at >= %s
                     ORDER BY a.scheduled_at ASC
                     LIMIT 10",
                    date('Y-m-d H:i:s')
                )
            );

            $stats = [
                'totalPatients' => (int) $totalPatients,
                'activeProviders' => (int) $activeProviders,
                'todayAppointments' => (int) $todayAppointments,
                'pendingAppointments' => (int) $pendingAppointments,
                'weekAppointments' => (int) $weekAppointments,
                'monthAppointments' => (int) $monthAppointments,
                'recentAppointments' => $recentAppointments,
            ];

            return new WP_REST_Response($stats, 200);

        } catch (Exception $e) {
            return new WP_REST_Response([
                'error' => 'Failed to fetch dashboard statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
