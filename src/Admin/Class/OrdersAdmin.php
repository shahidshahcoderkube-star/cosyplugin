<?php

namespace Cosy\Appointments\Admin;

class OrdersAdmin
{

  public function render_booking_orders(): void
  {
?>
    <div class="wrap cosy-orders">
      <h1 class="wp-heading-inline">Orders</h1>
      <a href="#" class="page-title-action">Add New Order</a>
      <hr class="wp-header-end">

      <!-- Filters -->
      <form method="get" class="cosy-orders-filters">
        <div class="tablenav top">
          <div class="alignleft actions">
            <label for="filter-status" class="screen-reader-text">Filter by status</label>
            <select name="status" id="filter-status">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>

            <label for="filter-provider" class="screen-reader-text">Filter by provider</label>
            <select name="provider" id="filter-provider">
              <option value="">All Providers</option>
              <option value="smith">Dr. Smith</option>
              <option value="patel">Dr. Patel</option>
            </select>

            <input type="date" name="from_date" placeholder="From">
            <input type="date" name="to_date" placeholder="To">

            <input type="submit" class="button" value="Filter">
          </div>
        </div>
      </form>

      <!-- Orders Table -->
      <table class="wp-list-table widefat fixed striped table-view-list cosy-orders-table">
        <thead>
          <tr>
            <td id="cb" class="manage-column column-cb check-column">
              <input type="checkbox">
            </td>
            <th scope="col" class="manage-column">Order ID</th>
            <th scope="col" class="manage-column">Customer</th>
            <th scope="col" class="manage-column">Provider</th>
            <th scope="col" class="manage-column">Service</th>
            <th scope="col" class="manage-column">Date &amp; Time</th>
            <th scope="col" class="manage-column">Amount</th>
            <th scope="col" class="manage-column">Status</th>
            <th scope="col" class="manage-column">Actions</th>
          </tr>
        </thead>
        <tbody id="the-list">
          <tr>
            <th scope="row" class="check-column"><input type="checkbox"></th>
            <td>#1023</td>
            <td>John Doe</td>
            <td>Dr. Smith</td>
            <td>Consultation</td>
            <td>02 Jan 2026, 5:00 PM</td>
            <td>₹1500</td>
            <td><span class="status confirmed">Confirmed</span></td>
            <td>
              <a href="#" class="button button-small">View</a>
              <a href="#" class="button button-small">Edit</a>
            </td>
          </tr>
          <tr>
            <th scope="row" class="check-column"><input type="checkbox"></th>
            <td>#1024</td>
            <td>Jane Roe</td>
            <td>Dr. Patel</td>
            <td>Therapy</td>
            <td>03 Jan 2026, 11:00 AM</td>
            <td>₹2000</td>
            <td><span class="status pending">Pending</span></td>
            <td>
              <a href="#" class="button button-small">View</a>
              <a href="#" class="button button-small">Edit</a>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td class="manage-column column-cb check-column"><input type="checkbox"></td>
            <th scope="col">Order ID</th>
            <th scope="col">Customer</th>
            <th scope="col">Provider</th>
            <th scope="col">Service</th>
            <th scope="col">Date &amp; Time</th>
            <th scope="col">Amount</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
          </tr>
        </tfoot>
      </table>

      <!-- Pagination -->
      <div class="tablenav bottom">
        <div class="tablenav-pages">
          <span class="displaying-num">Showing 1–20 of 356 orders</span>
          <span class="pagination-links">
            <a class="first-page button" href="#">«</a>
            <a class="prev-page button" href="#">‹</a>
            <span class="paging-input">1 of <span class="total-pages">18</span></span>
            <a class="next-page button" href="#">›</a>
            <a class="last-page button" href="#">»</a>
          </span>
        </div>
      </div>
    </div>


<?php
  }
}
