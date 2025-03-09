   <div class="main-content">
       <header>
           <h1>Returns</h1>
           <div class="search-profile">
               <?php include __DIR__ . '/searchbar.php'; ?>
               <?php include __DIR__ . '/profile.php'; ?>
           </div>
       </header>

       <div class="search-container">
           <input type="text" placeholder="Customer ID">
           <input type="text" placeholder="Sales ID">
           <input type="text" placeholder="Product ID">
           <input type="text" placeholder="Reason">
           <button class="search-btn">SEARCH</button>
           <button class="clear-btn">CLEAR</button>
       </div>

       <div class="returns-table">
           <div class="table-controls">
               <button class="create-btn">CREATE NEW <span>+</span></button>
               <button class="edit-btn">EDIT <span>✏️</span></button>
               <button class="delete-btn">DELETE <span>🗑️</span></button>
           </div>
           <table>
               <thead>
                   <tr>
                       <th><input type="checkbox"></th>
                       <th>ReturnID</th>
                       <th>SalesID</th>
                       <th>ProductID</th>
                       <th>Reason</th>
                       <th>Date</th>
                       <th>Status</th>
                   </tr>
               </thead>
               <tbody id="returns-table-body">
                   <!-- Data will be populated dynamically from the database -->
               </tbody>
           </table>
       </div>
   </div>