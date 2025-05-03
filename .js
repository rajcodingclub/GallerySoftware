function expandDiv() {
    document.getElementById("myDiv").style.width = "220px";
}

function shrinkDiv() {
    document.getElementById("myDiv").style.width = "62px";
}

function permanentDelete(){
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this imaginary file!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          swal("Poof! Your imaginary file has been deleted!", {
            icon: "success",
          });
        } else {
          swal("Your imaginary file is safe!");
        }
      });
      
}

  function addTag(tag) {
      var tagElement = $('<span class="tag">' + tag + '<span class="remove-tag">&times;</span></span>');
      $('.tag-input-container').prepend(tagElement);
  }

  // Function to update the hidden input field with selected tags
  function updateHiddenInput() {
      $('#hidden-tag-input').val(selectedTags.join('", "'));
  }

  // Handle removing a selected tag
  $(document).on('click', '.remove-tag', function() {
      var tagText = $(this).parent().text().slice(0, -1); // Remove the × character
      selectedTags = selectedTags.filter(t => t !== tagText);
      $(this).parent().remove();
      updateHiddenInput();
  });

  // Handle backspace for removing tags
  $('#tag-input').on('keydown', function(e) {
      if (e.key === 'Backspace' && $(this).val() === '') {
          var lastTag = $('.tag').last();
          if (lastTag.length > 0) {
              var tagText = lastTag.text().slice(0, -1); // Remove the × character
              selectedTags = selectedTags.filter(t => t !== tagText);
              lastTag.remove();
              updateHiddenInput();
          }
      }
  });


document.addEventListener('DOMContentLoaded', (event) => {
var popup = document.getElementById('image-popup');
var popupImage = document.getElementById('popup-image');
var clickableImages = document.querySelectorAll('.clickable-image');
var closeBtn = document.querySelector('.close');

clickableImages.forEach(function(image) {
  image.addEventListener('click', function() {
      popup.style.display = "block";
      popupImage.src = this.src;
  });
});

closeBtn.addEventListener('click', function() {
  popup.style.display = "none";
});

window.addEventListener('click', function(event) {
  if (event.target == popup) {
      popup.style.display = "none";
  }
});
});


function add_rows1() {
    var id = parseFloat($("#add_rows_id1").val());
   if(!id){
    id=0;
   }else{
    id = id + 1;
   }
  
    var row_id = 'row_' + id;
    var txt = '<div class="row" id="' + row_id + '"><div class="col-md-4"><div class="form-group">' + id + '.&nbsp;<label for="tag" class="form-label">Tag</label><select class="js-example-tags form-control form-select-sm" name="tag_' + id + '" id="tag_' + id + '" ><option value="">--- Select ---</option>';
    <?php
    $query = "SELECT upload_id, tag, district_name_english FROM upload_trans GROUP BY tag";
    $run = mysqli_query($db, $query);
    while ($data = mysqli_fetch_array($run)) {
        // Properly concatenate the PHP data into the JavaScript string
        echo 'txt += "<option value=\'' . $data['tag'] . '\'>' . addslashes($data['district_name_english']) . '</option>";' . "\n";
    }
    ?>
    txt += '</select></div></div><div class="col-md-1 d-flex justify-content- align-items-center"><button style="background-color: rgb(9, 9, 103); color:white; margin-top: 30px;" type="button" class="btn btn-info pull-right" onClick="add_rows1()">Add</button>&nbsp;&nbsp;<button style="background-color: rgb(202, 19, 19); color:white; margin-top: 30px;" type="button" class="btn btn-info pull-right" onClick="remove_row(\'' + row_id + '\')">Remove</button></div></div>';
    
    
    $("#test1").append(txt);
    $("#add_rows_id1").val(id);
  }
  
  function remove_row(row_id) {
    $("#" + row_id).remove();

    update_rows();
  }
  
  function update_rows() {
    var rows = $('#test1 .row');
    var num = parseFloat($("#add_rows_id_exist").val());
    if(!num){
        num=0;
    }
    
    var totalRows = (rows.length)+num;
    $("#add_rows_id1").val(totalRows);
  
    totalRows.each(function(index) {
        var newId = totalRows - index;  
        $(this).attr("id", "row_" + newId);  
        $(this).find(".form-group").html(newId + '.&nbsp;<label for="tag" class="form-label">Tag</label><input type="text" class="form-control form-select-sm" name="tag_' + newId + '" id="tag_' + newId + '" value="">');  // Update row label and input
        $(this).find("button").last().attr("onClick", "remove_row('row_" + newId + "')");  // Update the remove button's onClick handler
    });
}

   
  
  
$(function() {
  $("#album").autocomplete({
      source: function(request, response) {
          $.ajax({
              url: "scripts/ajax1.php",
              type: "GET",
              data: {
                  term: request.term
              },
              success: function(data) {
                  console.log("Received data: ", data);  // Debugging statement
                  response(JSON.parse(data));
              },
              error: function(jqXHR, textStatus, errorThrown) {
                  console.error("AJAX error: ", textStatus, errorThrown);  // Debugging statement
              }
          });
      },
      minLength: 1,
      select: function(event, ui) {
          $("#album").val(ui.item.value);
          return false;
      },
      focus: function(event, ui) {
          $("#album").val(ui.item.value);
          return false;
      }
  });

  $("#album").keydown(function(event) {
      if (event.keyCode == 13) {  // Enter key
          var autocomplete = $(this).data("ui-autocomplete");
          var menuElement = autocomplete.menu.element;
          var firstItem = menuElement.find("li.ui-menu-item:first .ui-menu-item-wrapper").text();
          if (firstItem) {
              $(this).val(firstItem);
              autocomplete.close(); 
          }
          event.preventDefault();  // Prevent form submission
      }
  });
});

function toggleImages(department) {
    const albumIcon = document.getElementById('icon-' + department);
    const imagesDiv = document.getElementById('images-' + department);
    const backButton = document.getElementById('back-' + department);
    
    albumIcon.style.display = 'none';
    imagesDiv.style.display = 'flex';
    backButton.style.display = 'inline';
}

function goBack(department) {
    const albumIcon = document.getElementById('icon-' + department);
    const imagesDiv = document.getElementById('images-' + department);
    const backButton = document.getElementById('back-' + department);
    
    albumIcon.style.display = 'block';
    imagesDiv.style.display = 'none';
    backButton.style.display = 'none';
}