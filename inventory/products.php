<?php
// This file is part of FLIP Plugins for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Product catalog management interface.
 *
 * @package     local_equipment
 * @copyright   2024 onwards Joshua Kirby <josh@funlearningcompany.com>
 * @author      Joshua Kirby
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Require login and check capabilities
require_login();
require_capability('local/equipment:manageinventory', context_system::instance());

// Set up admin external page
admin_externalpage_setup('local_equipment_inventory_products');

// Handle form submission
$action = optional_param('action', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);
$name = optional_param('name', '', PARAM_TEXT);
$description = optional_param('description', '', PARAM_TEXT);
$manufacturer = optional_param('manufacturer', '', PARAM_TEXT);
$model = optional_param('model', '', PARAM_TEXT);
$category = optional_param('category', '', PARAM_TEXT);
$is_consumable = optional_param('is_consumable', 0, PARAM_INT);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('manageproducts', 'local_equipment'));

// Process actions
if ($action) {
    try {
        global $DB;

        if ($action === 'add' && $name) {
            $product = new stdClass();
            $product->name = $name;
            $product->description = $description;
            $product->manufacturer = $manufacturer;
            $product->model = $model;
            $product->category = $category;
            $product->is_consumable = $is_consumable;
            $product->active = 1;
            $product->timecreated = time();
            $product->timemodified = time();

            $DB->insert_record('local_equipment_products', $product);
            echo $OUTPUT->notification('Product added successfully!', 'success');
        } elseif ($action === 'edit' && $id && $name) {
            $product = $DB->get_record('local_equipment_products', ['id' => $id]);
            if ($product) {
                $product->name = $name;
                $product->description = $description;
                $product->manufacturer = $manufacturer;
                $product->model = $model;
                $product->category = $category;
                $product->is_consumable = $is_consumable;
                $product->timemodified = time();

                $DB->update_record('local_equipment_products', $product);
                echo $OUTPUT->notification('Product updated successfully!', 'success');
            }
        } elseif ($action === 'delete' && $id) {
            // Check if product has any items
            $item_count = $DB->count_records('local_equipment_items', ['productid' => $id]);
            if ($item_count > 0) {
                echo $OUTPUT->notification("Cannot delete product: {$item_count} equipment items are using this product.", 'error');
            } else {
                $DB->delete_records('local_equipment_products', ['id' => $id]);
                echo $OUTPUT->notification('Product deleted successfully!', 'success');
            }
        } elseif ($action === 'toggle' && $id) {
            $product = $DB->get_record('local_equipment_products', ['id' => $id]);
            if ($product) {
                $product->active = $product->active ? 0 : 1;
                $product->timemodified = time();
                $DB->update_record('local_equipment_products', $product);
                $status = $product->active ? 'activated' : 'deactivated';
                echo $OUTPUT->notification("Product {$status} successfully!", 'success');
            }
        }
    } catch (Exception $e) {
        echo $OUTPUT->notification('Error: ' . $e->getMessage(), 'error');
    }
}

// Get product for editing if specified
$edit_product = null;
if ($action === 'edit' && $id) {
    $edit_product = $DB->get_record('local_equipment_products', ['id' => $id]);
}

// Add/Edit Product Form
echo html_writer::tag('h3', $edit_product ? 'Edit Product' : 'Add New Product');

echo html_writer::start_tag('form', ['method' => 'post', 'action' => '', 'class' => 'row g-3']);

echo html_writer::start_div('col-md-6');
echo html_writer::tag('label', get_string('productname', 'local_equipment'), ['for' => 'name', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'name',
    'name' => 'name',
    'value' => $edit_product ? $edit_product->name : '',
    'class' => 'form-control',
    'required' => true
]);
echo html_writer::end_div();

echo html_writer::start_div('col-md-6');
echo html_writer::tag('label', get_string('manufacturer', 'local_equipment'), ['for' => 'manufacturer', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'manufacturer',
    'name' => 'manufacturer',
    'value' => $edit_product ? $edit_product->manufacturer : '',
    'class' => 'form-control'
]);
echo html_writer::end_div();

echo html_writer::start_div('col-md-6');
echo html_writer::tag('label', get_string('model', 'local_equipment'), ['for' => 'model', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'model',
    'name' => 'model',
    'value' => $edit_product ? $edit_product->model : '',
    'class' => 'form-control'
]);
echo html_writer::end_div();

echo html_writer::start_div('col-md-6');
echo html_writer::tag('label', get_string('category', 'local_equipment'), ['for' => 'category', 'class' => 'form-label']);
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'id' => 'category',
    'name' => 'category',
    'value' => $edit_product ? $edit_product->category : '',
    'class' => 'form-control',
    'placeholder' => 'e.g., robotics, electronics, consumable'
]);
echo html_writer::end_div();

echo html_writer::start_div('col-12');
echo html_writer::tag('label', get_string('description', 'local_equipment'), ['for' => 'description', 'class' => 'form-label']);
echo html_writer::tag('textarea', $edit_product ? $edit_product->description : '', [
    'id' => 'description',
    'name' => 'description',
    'class' => 'form-control',
    'rows' => 3
]);
echo html_writer::end_div();

echo html_writer::start_div('col-12');
echo html_writer::start_div('form-check');
echo html_writer::empty_tag('input', [
    'type' => 'checkbox',
    'id' => 'is_consumable',
    'name' => 'is_consumable',
    'value' => 1,
    'class' => 'form-check-input',
    'checked' => $edit_product && $edit_product->is_consumable ? true : false
]);
echo html_writer::tag('label', get_string('isconsumable', 'local_equipment'), ['for' => 'is_consumable', 'class' => 'form-check-label']);
echo html_writer::tag('small', 'Check if this product is consumable (not returned after use)', ['class' => 'form-text text-muted']);
echo html_writer::end_div();
echo html_writer::end_div();

if ($edit_product) {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $edit_product->id]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'edit']);
} else {
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'add']);
}

echo html_writer::start_div('col-12');
echo html_writer::tag('button', $edit_product ? 'Update Product' : 'Add Product', [
    'type' => 'submit',
    'class' => 'btn btn-primary'
]);
if ($edit_product) {
    echo ' ';
    echo html_writer::link(
        new moodle_url('/local/equipment/inventory/products.php'),
        'Cancel',
        ['class' => 'btn btn-secondary']
    );
}
echo html_writer::end_div();

echo html_writer::end_tag('form');

// Product List
echo html_writer::tag('h3', 'Product Catalog', ['class' => 'mt-4']);

try {
    global $DB;
    $products = $DB->get_records_sql("
        SELECT p.*,
               COUNT(ei.id) as item_count,
               COUNT(CASE WHEN ei.status = 'available' THEN 1 END) as available_count,
               COUNT(CASE WHEN ei.status = 'checked_out' THEN 1 END) as checked_out_count
        FROM {local_equipment_products} p
        LEFT JOIN {local_equipment_items} ei ON p.id = ei.productid
        GROUP BY p.id, p.name, p.description, p.manufacturer, p.model, p.category, p.is_consumable, p.active, p.timecreated, p.timemodified
        ORDER BY p.active DESC, p.name ASC
    ");

    if ($products) {
        echo html_writer::start_tag('div', ['class' => 'table-responsive']);
        echo html_writer::start_tag('table', ['class' => 'table table-striped']);
        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');
        echo html_writer::tag('th', 'Name');
        echo html_writer::tag('th', 'Manufacturer');
        echo html_writer::tag('th', 'Category');
        echo html_writer::tag('th', 'Type');
        echo html_writer::tag('th', 'Items');
        echo html_writer::tag('th', 'Available');
        echo html_writer::tag('th', 'Checked Out');
        echo html_writer::tag('th', 'Status');
        echo html_writer::tag('th', 'Actions');
        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
        echo html_writer::start_tag('tbody');

        foreach ($products as $product) {
            echo html_writer::start_tag('tr', ['class' => $product->active ? '' : 'table-secondary']);

            echo html_writer::start_tag('td');
            echo html_writer::tag('strong', $product->name);
            if ($product->model) {
                echo html_writer::tag('br');
                echo html_writer::tag('small', 'Model: ' . $product->model, ['class' => 'text-muted']);
            }
            echo html_writer::end_tag('td');

            echo html_writer::tag('td', $product->manufacturer ?: '-');
            echo html_writer::tag('td', $product->category ?: '-');
            echo html_writer::tag('td', $product->is_consumable ? 'Consumable' : 'Returnable');
            echo html_writer::tag('td', $product->item_count);
            echo html_writer::tag('td', $product->available_count);
            echo html_writer::tag('td', $product->checked_out_count);

            $status_class = $product->active ? 'text-success' : 'text-muted';
            $status_text = $product->active ? 'Active' : 'Inactive';
            echo html_writer::tag('td', $status_text, ['class' => $status_class]);

            echo html_writer::start_tag('td');

            // Edit button
            echo html_writer::link(
                new moodle_url('/local/equipment/inventory/products.php', ['action' => 'edit', 'id' => $product->id]),
                'Edit',
                ['class' => 'btn btn-sm btn-outline-primary me-1']
            );

            // Toggle active/inactive button
            $toggle_text = $product->active ? 'Deactivate' : 'Activate';
            $toggle_class = $product->active ? 'btn-outline-warning' : 'btn-outline-success';
            echo html_writer::link(
                new moodle_url('/local/equipment/inventory/products.php', ['action' => 'toggle', 'id' => $product->id]),
                $toggle_text,
                ['class' => "btn btn-sm {$toggle_class} me-1"]
            );

            // Delete button (only if no items)
            if ($product->item_count == 0) {
                echo html_writer::link(
                    new moodle_url('/local/equipment/inventory/products.php', ['action' => 'delete', 'id' => $product->id]),
                    'Delete',
                    [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'onclick' => 'return confirm("Are you sure you want to delete this product?");'
                    ]
                );
            }

            echo html_writer::end_tag('td');
            echo html_writer::end_tag('tr');
        }

        echo html_writer::end_tag('tbody');
        echo html_writer::end_tag('table');
        echo html_writer::end_tag('div');
    } else {
        echo html_writer::tag('p', 'No products found. Add your first product above.', ['class' => 'text-muted']);
    }
} catch (Exception $e) {
    echo html_writer::tag('p', 'Error loading products: ' . $e->getMessage(), ['class' => 'text-danger']);
}

echo $OUTPUT->footer();
