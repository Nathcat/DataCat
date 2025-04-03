let slidingEntry_entryFieldIds = [];
let slidingEntry_currentEntryId = 0;
let slidingEntry_finished_entry_callback = () => {};

function slidingEntry_nextEntry() {
    if (slidingEntry_currentEntryId == slidingEntry_entryFieldIds.length - 1) {
        slidingEntry_finished_entry_callback();
        return;
    }

    slidingEntry_currentEntryId++;

    for (let i = 0; i < slidingEntry_entryFieldIds.length; i++) {
        $("#" + slidingEntry_entryFieldIds[i]).animate({
            left: ((i - slidingEntry_currentEntryId) * 100) + "%"
        }, 250, () => {
            document.getElementById(slidingEntry_entryFieldIds[slidingEntry_currentEntryId]).focus();
        });
    }
}

function slidingEntry_prevEntry() {
    if (slidingEntry_currentEntryId == 0) { return; }

    slidingEntry_currentEntryId--;

    for (let i = 0; i < slidingEntry_entryFieldIds.length; i++) {
        $("#" + slidingEntry_entryFieldIds[i]).animate({
            left: ((i - slidingEntry_currentEntryId) * 100) + "%"
        }, 250, () => {
            document.getElementById(slidingEntry_entryFieldIds[slidingEntry_currentEntryId]).focus();
        });
    }
}

function slidingEntry_setup(entryIds) {
    slidingEntry_entryFieldIds = entryIds;
    slidingEntry_currentEntryId = 0;

    window.onclick = (e) => {
        document.getElementById(slidingEntry_entryFieldIds[slidingEntry_currentEntryId]).focus();
    };    

    for (let i = 0; i < slidingEntry_entryFieldIds.length; i++) {
        document.getElementById(slidingEntry_entryFieldIds[i]).addEventListener("keypress", (e) => {
            if (e.target.id !== slidingEntry_entryFieldIds[slidingEntry_currentEntryId]) return;
    
            if (e.key == "Enter") slidingEntry_nextEntry();
        });
    
        document.getElementById(slidingEntry_entryFieldIds[i]).onkeydown = (e) => {
            if (e.target.id !== slidingEntry_entryFieldIds[slidingEntry_currentEntryId]) return;
    
            let key = e.keyCode || e.charCode;
    
            if (key == 8 || key == 46) {
                if (document.getElementById(slidingEntry_entryFieldIds[i]).value == "") {
                    slidingEntry_prevEntry();
                }
            }
        }
    }

    document.getElementById(slidingEntry_entryFieldIds[slidingEntry_currentEntryId]).focus();
}