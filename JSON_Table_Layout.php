<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JSON Data Table</title>
    <style>

        /* Error Messages Styling */

        .table-err-msg {
            max-width: 800px;
            padding: 100px 4%;
            color: red; 
            text-align: center; 
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.75rem;
            height: 100%; 
            display: flex; 
            flex-direction: column;
            margin: 0 auto;
            align-items: center; 
            justify-content: center;
        }
        .table-err-msg span {
            width: 100%;
            margin-bottom: 1.5rem;
            font-size: 2.25rem;
            line-height: 3rem;
        }
    </style>
    <?php 

        // Request Params.  Get Required Url Parameter And Optional Headers Parameter

        $uri = $_SERVER['REQUEST_URI'];
        $uri_components = parse_url($uri);
        parse_str($uri_components['query'], $params);
        $url = $params['url'];
        $headers = $params['headers'];

        // If Url Parameter Not Found, Throw Error

        if (!$url) { 
            exit('
                <div class="table-err-msg">
                    <span>Error</span> A url parameter must be provided with url to get data from.
                </div>
            ');
        }

        // Get Data From Url

        $json = @file_get_contents($url);

        // If HTTP Data Fails, Throw Error

        if ($json === false) {
            $err = error_get_last();
            exit('
                <div class="table-err-msg">
                    <span>Error Loading Table Data.</span> '. error_get_last()['message'] .'.
                </div>
            ');
        }

        $data = json_decode($json, true);

        // Remove Keys By Name (Optional)

        foreach($data as $index => $row) {
            unset($data[$index]['albumId']);
        }

        // Get Keys

        $column_keys = array_keys($data[0]);
        $columns_qt = count($column_keys);

        $keys_match = true;

        // Check That Keys Are Consistent Throughout.  If Not, Set $keys_match To False

        foreach($data as $item) {
            if (count(array_keys($item)) !== $columns_qt) {
                $keys_match = false;
            }
            foreach(array_keys($item) as $key) {
                if (!in_array($key, $column_keys)) {
                    $keys_match = false;
                }
            }
        }

        // If Total Keys Is Not Divisible By Two, Set $keys_match To False

        if ($columns_qt % 2 !== 0) {
            $keys_match = false;
        }

        // If $keys_match Is False, Throw Error

        if (!$keys_match) {
            exit('
                <div class="table-err-msg">
                    Number Of Columns(Keys) Must Be An Even Number And Be Consistent For Each Row(Object) Entry.
                </div>
            ');
        }

        // Generate Table Root Container Div Id

        $table_rand_num = rand(1, 9999);
        $table_root_id = "table-container-{$table_rand_num}";

        // Set Max Width Of Table 

        $max_width = ''.($columns_qt * 19) . 'rem';

        // Table CSS

        echo '
            <style>
                body {
                    margin: 4rem 4%;
                }
                #'. $table_root_id .' {
                    margin: auto;
                    max-width: '. $max_width .';
                    border-left: 1px black solid;
                    border-right: 1px black solid;
                }

                #'. $table_root_id .' .table-row {
                    display: grid;
                    grid-template-columns: repeat('. $columns_qt .', 1fr);
                    border-bottom: 1px black solid;
                    transition: 0.15s;
                    gap: 1rem;
                    padding: 0.675rem 1rem;
                }

                #'. $table_root_id .' .table-row:not(.table-heading):nth-of-type(even) {
                    background: #e9e9e9;
                }

                #'. $table_root_id .' .table-row:hover:not(.table-heading) {
                    background: orange;
                    cursor: pointer;
                }

                #'. $table_root_id .'  .table-heading {
                    padding: 1rem 1rem;
                    position: sticky;
                    top: 0;
                }


                #'. $table_root_id .'  .heading-cell-wrapper svg {
                    transition: 0.15s;
                }

                #'. $table_root_id .'  .heading-cell-wrapper:hover, .heading-cell-wrapper:hover svg g {
                    fill: orange !important;
                    cursor: pointer;
                }

                #'. $table_root_id .'  .heading-cell-wrapper svg g {
                    opacity: 0.25;
                }

                #'. $table_root_id .'  .arrow-ascending {
                    transform: rotate(180deg);
                }

                #'. $table_root_id .'  .arrow-activated g {
                    opacity: 1 !important;
                    fill: orange !important;
                }

                #'. $table_root_id .'  .arrow-activated h4 {
                    color: orange !important;
                }

                #'. $table_root_id .'  .table-heading {
                    background: black;
                    color: white;
                    font-size: 1.25rem !important;
                }
                
                #'. $table_root_id .'  .table-rows-container {
                    display: grid;
                    grid-auto-rows: 1fr;
                }

                #'. $table_root_id .'  .table-row > * {
                    margin: 0;
                    width: auto;
                    text-align: center;
                }

                #'. $table_root_id .'  .table-row p {
                    font-size: 1rem;
                    line-height: 1.25rem;
                }

                #'. $table_root_id .'  .heading-cell-wrapper {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    gap: 0.5rem;
                    padding-left: 1.25rem;
                }

                #'. $table_root_id .'  .heading-cell-wrapper svg {
                    width: 0.75rem;
                    height: 0.75rem;
                }

                #'. $table_root_id .'  .heading-cell-wrapper > * {
                    margin: 0;
                }

                #'. $table_root_id .'  .mobile-cell {
                    margin: 0;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    gap: 0.5rem;
                }

                #'. $table_root_id .'  .mobile-cell p {
                    margin: 0;
                }

                #'. $table_root_id .'  .mobile-cell > span {
                    display: none;
                }

                #'. $table_root_id .'-modal {
                    width: 100vw;
                    height: 100vh;
                    position: fixed;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    top: 0;
                    left: 0;
                    z-index: 100;
                }

                #'. $table_root_id .'-modal .overlay {
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    left: 0;
                    top: 0;
                    background: black;
                    opacity: 0.9;
                    z-index: 1;
                }

                #'. $table_root_id .'-modal .modal-container {
                    max-width: 48rem;
                    background: white;
                    z-index: 2;
                    margin: 6.25rem 4%;
                    padding: 2.5rem 4%;
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    gap: 2rem;
                }

                #'. $table_root_id .'-modal .modal-field h4 {
                    text-align: center;
                    text-decoration: underline;
                    font-size: 1.25rem;
                    margin: 0 0 0.75rem;;
                }

                #'. $table_root_id .'-modal .modal-field p {
                    text-align: center;
                    font-size: 1rem;
                    margin: 0;
                }
                #'. $table_root_id .'-modal .modal-close-button {
                    font-size: 24px;
                    position: absolute;
                    top: 0;
                    left: 100%;
                    padding: 12px;
                    cursor: pointer;
                    transform: translateX(-100%);
                }
                .scroll-hidden {
                    overflow: hidden !important;
                }
                @media(max-width: '. ($columns_qt * 18) .'rem) {
                    #'. $table_root_id .'  .table-row {
                        grid-template-columns: repeat('. $columns_qt / 2 .', 1fr);
                        grid-row-gap: 1.5rem !important;
                        padding: 1.5rem 4%;
                    }
                    #'. $table_root_id .'  .mobile-cell span {
                        display: block !important;
                        font-weight: 700;
                        font-size: 1.25rem;
                        text-decoration: underline;
                    }
                    #'. $table_root_id .'  .table-heading {
                        padding: 1rem 4% !important;
                        position: sticky;
                    }
                    #'. $table_root_id .'  .cells-wrapper {
                        gap: 2rem;
                        padding: 1rem;
                        flex-wrap: nowrap;
                        align-items: start;
                    }
                    #'. $table_root_id .'  .cells-wrapper > * {
                        margin: 0;
                    }
                    #'. $table_root_id .'  .table-row {
                        gap: 0;
                    }
                }
                @media(max-width: '. ($columns_qt * 12) .'rem) {
                    #'. $table_root_id .'  .table-row {
                        grid-template-columns: repeat('. ($columns_qt % 4 === 0 ? $columns_qt / 4 : $columns_qt / 3) .', 1fr); 
                        grid-row-gap: 1.5rem !important;
                        padding: 1.5rem 4%;
                    }
                    #'. $table_root_id .'  .mobile-cell span {
                        display: block !important;
                        font-weight: 700;
                        font-size: 1.25rem;
                        text-decoration: underline;
                    }
                    #'. $table_root_id .'  .table-heading {
                        padding: 1rem 4% !important;
                        position: sticky;
                    }
                    #'. $table_root_id .'  .cells-wrapper {
                        gap: 2rem;
                        padding: 1rem;
                        flex-wrap: nowrap;
                        align-items: start;
                    }
                    #'. $table_root_id .'  .cells-wrapper > * {
                        margin: 0;
                    }
                    #'. $table_root_id .'  .table-row {
                        gap: 0;
                    }
                }
                @media(max-width: '. ($columns_qt * 6.5) .'rem) {
                    #'. $table_root_id .'  .table-row {
                        grid-template-columns: 1fr;
                        grid-row-gap: 1.5rem !important;
                        padding: 1.5rem 4%;
                    }
                    #'. $table_root_id .'  .mobile-cell span {
                        display: block !important;
                        font-weight: 700;
                        font-size: 1.25rem;
                        text-decoration: underline;
                    }
                    #'. $table_root_id .'  .table-heading {
                        padding: 1rem 4% !important;
                        position: static !important;
                    }
                    #'. $table_root_id .'  .cells-wrapper {
                        gap: 2rem;
                        padding: 1rem;
                        flex-wrap: nowrap;
                        align-items: start;
                    }
                    #'. $table_root_id .'  .cells-wrapper > * {
                        margin: 0;
                    }
                    #'. $table_root_id .'  .table-row {
                        gap: 0;
                    }
                }
            </style>
        '; 

        // Limit Number Of Characters Output String

        function char_length_limiter($value) {
            if (strlen($value) > 28) {
                return substr($value, 0, 28) . '...';
            } else return $value;
        }
        
    ?>
</head>
<body>
    <section id="<?php echo $table_root_id ?>">
        <div class="table-row table-heading">
            <?php 
                foreach($column_keys as $column) {
                    echo '
                        <div class="heading-cell-wrapper" data-headcontainer="true" title="'. ucfirst($column) .'">
                            <h4 data-heading="true" data-order="descending" data-column="'.$column.'">'. ucfirst($column) .'</h4>
                            <svg version="1.0" xmlns="http://www.w3.org/2000/svg" data-hideonidle="true" data-lightboxarrowdirection="right" data-lightboxarrow="true" width="100.000000pt" height="100.000000pt" viewBox="0 0 100.000000 100.000000" preserveAspectRatio="xMidYMid meet">
                                <g data-hideonidle="true" transform="translate(100.000000,100.000000) scale(0.100000,-0.100000) rotate(90.000)" fill="#ffffff" stroke="none">
                                <path data-hideonidle="true" d="M415 720 l-220 -220 223 -222 222 -223 72 73 73 72 -148 148 -147 147 145 145 c80 80 145 149 145 155 0 0 -140 145 -140 145 0 0 -104 -99 -225 -220z"></path>
                                </g>
                            </svg>
                        </div>
                    ';
                }
            ?>
        </div>
        <div data-tablerowsinsertion="true" class="table-rows-container">
            <?php foreach($data as $row): ?>
                <div class="table-row" data-row="true" data-id="<?php echo $row['id'] ?>">
                        <?php
                            foreach(array_keys($row) as $key) {
                                echo '
                                    <div class="mobile-cell" data-cellcontainer="true" data-column="'. $key .'" title="'. $row[$key] .'">
                                        <span data-heading="true" data-order="descending" data-column="'. $key .'">'. ucfirst($key) .': </span>
                                        <p>'. char_length_limiter($row[$key]) .'</p>
                                    </div>
                                ';
                            }
                        ?>
                </div>
            <?php endforeach ?>
        </div>
    </section>

    <script>

    // Select All Table Rows

    let rows;

    // Monitor Click Of Table Rows And Perform Action On Click

    function monitorRowsClick() {

        rows = document.querySelectorAll(`#<?php echo $table_root_id ?> [data-row="true"]`);

        rows.forEach(row => {
            row.addEventListener('click', () => {
                const output = { };
                const columns = row.querySelectorAll('[data-cellcontainer="true"]');
                columns.forEach(col => {
                    output[col.dataset.column] = col.title;
                });

                // Add Modal

                const modal = document.createElement('div');
                modal.id = '<?php echo $table_root_id ?>-modal';
                modal.innerHTML = `
                    <div data-overlay="true" class="overlay"></div>
                    <div class="modal-container">
                        <div data-modalclose="true"class="modal-close-button">X</div>
                        ${Object.entries(output).map(([key, val]) => `
                            <div class="modal-field">
                                <h4>${key[0].toUpperCase() + key.slice(1).toLowerCase()}</h4>
                                <p>${val}</p>
                            </div>
                        `).join('')}
                    </div>
                `;

                document.body.appendChild(modal);
                document.body.classList.add("scroll-hidden");

                // Remove Modal

                function closeModal() {
                    document.body.classList.remove("scroll-hidden");
                    const modal = document.querySelector('#<?php echo $table_root_id ?>-modal');
                    if (modal) {
                        modal.remove();
                    }
                }

                // Close Modal On Escape Key Press

                window.addEventListener('keyup', e => {
                    if (e.key === 'Escape') {
                        closeModal();
                    }
                });

                // Close Modal On Overlay Click

                document.querySelector('#<?php echo $table_root_id ?>-modal [data-overlay="true"]')
                    .addEventListener('click', closeModal);

                // Close Modal On "X" Click

                document.querySelector('#<?php echo $table_root_id ?>-modal [data-modalclose="true"]')
                    .addEventListener('click', closeModal);
            });
        });
    }

    monitorRowsClick();

    // Monitor Table Heading Clicks

    document.querySelectorAll('[data-headcontainer="true"]').forEach(heading => {
        heading.addEventListener('click', () => 
            tableSorter(heading)                    
        );
    });

    // Sort Table Rows Based On Which Column User Clicked On.  Alternates Shifting Between Ascending And Descending Depending On Last Sort State.  Starting Default Is "descending"

    function tableSorter(container) {

        const rowArr = [...rows];

        // Select Column Heading Arrow Icon

        const arrow = container.querySelector('svg');

        // Clears Out Arrow Highlights From Any Previous Sorting Clicks Made

        document.querySelectorAll('[data-headcontainer="true"]').forEach(head => {
            head.classList.remove('arrow-activated');
        });

        // Highlights Arrow That Was Clicked

        container.classList.add('arrow-activated');

        const heading = container.querySelector('h4');
        const column = heading.dataset.column;

        // Sorts Data

        rowArr.sort((a, b) => {
            const aTitle = a.querySelector(`[data-column="${column}"]`).title;
            const bTitle = b.querySelector(`[data-column="${column}"]`).title;

            let aVal;
            let bVal;

            if (/^\d+$/.test(aTitle)) {
                aVal = Number(aTitle);
            } else aVal = aTitle;

            if (/^\d+$/.test(bTitle)) {
                bVal = Number(bTitle);
            } else bVal = bTitle;

            if (heading.dataset.order === 'ascending') {
                return aVal > bVal ? 1 : -1;
            } else {
                return aVal > bVal ? -1 : 1  ; 
            }
        });

        if (heading.dataset.order === 'ascending') {
            heading.dataset.order = 'descending';
            arrow.classList.add('arrow-ascending');
        } else {
            heading.dataset.order = 'ascending';
            arrow.classList.remove('arrow-ascending');
        }

        // Rerenders Table Rows To Reflect New Sorting 

        document.querySelector(`#<?php echo $table_root_id ?> [data-tablerowsinsertion="true"]`).innerHTML = rowArr.map(row => row.outerHTML).join('');
        
        // Refreshes Monitoring Of Table Rows Clicked After Rerender

        monitorRowsClick();
    }

    </script>
</body>
</html>

