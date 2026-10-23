<div class="perPage">
    <form method="POST">
        <input type="hidden" name="setPerPage" value="1" />
        <select name="perPage" onchange="$(this).closest('form').submit();">
            <option value="">all</option>
            <option <?= $iPerPage == 5 ? 'SELECTED' : '' ?> value="5">5</option>
            <option <?= $iPerPage == 10 ? 'SELECTED' : '' ?> value="10">10</option>
            <option <?= $iPerPage == 25 ? 'SELECTED' : '' ?> value="25">25</option>
            <option <?= $iPerPage == 50 ? 'SELECTED' : '' ?> value="50">50</option>
            <option <?= $iPerPage == 100 ? 'SELECTED' : '' ?> value="100">100</option>
        </select> per page
    </form>
</div>