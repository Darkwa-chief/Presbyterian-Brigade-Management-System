<?php
// includes/functions.php

function formatDate($date, $format = 'M d, Y') {
    if (empty($date) || $date === '0000-00-00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'M d, Y h:i A') {
    if (empty($datetime)) {
        return 'N/A';
    }
    return date($format, strtotime($datetime));
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge badge-success">Active</span>',
        'inactive' => '<span class="badge badge-secondary">Inactive</span>',
        'suspended' => '<span class="badge badge-danger">Suspended</span>',
        'scheduled' => '<span class="badge badge-primary">Scheduled</span>',
        'ongoing' => '<span class="badge badge-warning">Ongoing</span>',
        'completed' => '<span class="badge badge-success">Completed</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
        'present' => '<span class="badge badge-success">Present</span>',
        'absent' => '<span class="badge badge-danger">Absent</span>',
        'excused' => '<span class="badge badge-warning">Excused</span>',
        'late' => '<span class="badge badge-info">Late</span>',
        'transferred' => '<span class="badge badge-info">Transferred</span>',
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

function exportToCSV($data, $filename, $headers = []) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($headers)) {
        fputcsv($output, $headers);
    } elseif (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function paginate($totalItems, $currentPage, $itemsPerPage) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset
    ];
}

function renderPagination($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($pagination['current_page'] > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($pagination['current_page'] - 1) . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        if ($i == $pagination['current_page']) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($pagination['current_page'] < $pagination['total_pages']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($pagination['current_page'] + 1) . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}