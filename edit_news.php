<?php include("includes/header.php");

	require("includes/function.php");
	require("language/language.php");

	require_once("thumbnail_images.class.php");

  //Get Category
	$cat_qry="SELECT * FROM tbl_category ORDER BY category_name";
	$cat_result=mysqli_query($mysqli,$cat_qry); 

  $qry="SELECT * FROM tbl_news where id='".$_GET['news_id']."'";
  $result=mysqli_query($mysqli,$qry);
  $row=mysqli_fetch_assoc($result);

   //Gallery Images
    $qry1="SELECT * FROM tbl_news_gallery where news_id='".$_GET['news_id']."'";
    $result1=mysqli_query($mysqli,$qry1);
	
	if(isset($_POST['submit']))
	{
        
        
        if ($_POST['news_type']=='video')
        {

              $video_url=$_POST['video_url'];

              $youtube_video_url = addslashes($_POST['video_url']);
              parse_str( parse_url( $youtube_video_url, PHP_URL_QUERY ), $array_of_vars );
              $video_id=  $array_of_vars['v'];

              $news_image='';     

        }

         

        if ($_POST['news_type']=='image')
        {

            if($_FILES['news_image']['name']!="")
            {
              $news_image=rand(0,99999)."_".$_FILES['news_image']['name'];
       
              //Main Image
              $tpath1='images/'.$news_image;        
              $pic1=compress_image($_FILES["news_image"]["tmp_name"], $tpath1, 80);
         
              //Thumb Image 
              $thumbpath='images/thumbs/'.$news_image;   
              $thumb_pic1=create_thumb_image($tpath1,$thumbpath,'200','200');   

              $video_url='';
              $video_id='';
            }
            else
            {
                $news_image=$_POST['news_thumbnail_name'];
            }
        } 


          
        $data = array( 
          'cat_id'  =>  $_POST['cat_id'],
          'news_type'  =>  $_POST['news_type'],
          'news_title'  =>  addslashes($_POST['news_title']),
          'video_url'  =>  $video_url,
          'video_id'  =>  $video_id,
          'news_image'  =>  $news_image,
          'news_description'  =>  addslashes($_POST['news_description']),
          'news_date'  =>  strtotime($_POST['news_date'])
          );    
	 
    $qry=Update('tbl_news', $data, "WHERE id = '".$_POST['news_id']."'");

    $news_id=$_POST['news_id'];
    $size_sum = array_sum($_FILES['news_gallery_image']['size']);
     
  if($size_sum > 0)
   { 
      for ($i = 0; $i < count($_FILES['news_gallery_image']['name']); $i++) 
      {
           $file_name= str_replace(" ","-",$_FILES['news_gallery_image']['name'][$i]);
           $news_gallery_image=rand(0,99999)."_".$file_name;
         
           //Main Image
           $tpath1='images/'.$news_gallery_image;       
           $pic1=compress_image($_FILES["news_gallery_image"]["tmp_name"][$i], $tpath1, 80);

            $data1 = array(
                'news_id'=>$news_id,
                'news_gallery_image'  => $news_gallery_image                         
                );      

            $qry1 = Insert('tbl_news_gallery',$data1); 

      }
    }

         
		$_SESSION['msg']="11"; 
		header( "Location:edit_news.php?news_id=".$_POST['news_id']);
		exit;	

		 
	}
	
   //Delete gallery image
  if(isset($_GET['image_id']))
  {
        $img_rss=mysqli_query($mysqli,'SELECT * FROM tbl_news_gallery WHERE id=\''.$_GET['image_id'].'\'');
      $img_rss_row=mysqli_fetch_assoc($img_rss);
      
      if($img_rss_row['news_gallery_image']!="")
        {
          unlink('images/'.$img_rss_row['news_gallery_image']);
           
      }
  
    Delete('tbl_news_gallery','id='.$_GET['image_id'].'');
    
    
    header( "Location:edit_news.php?news_id=".$_GET['news_id']);
    exit; 
  } 


  $ck_file_path = 'http://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']).'';
	  
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script>
            $(function () {
                $('#btn').click(function () {
                    $('.myprogress').css('width', '0');
                    $('.msg').text('');
                    var video_local = $('#video_local').val();
                    if (video_local == '') {
                        alert('Please enter file name and select file');
                        return;
                    }
                    var formData = new FormData();
                    formData.append('video_local', $('#video_local')[0].files[0]);
                    $('#btn').attr('disabled', 'disabled');
                     $('.msg').text('Uploading in progress...');
                    $.ajax({
                        url: 'uploadscript.php',
                        data: formData,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        // this part is progress bar
                        xhr: function () {
                            var xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener("progress", function (evt) {
                                if (evt.lengthComputable) {
                                    var percentComplete = evt.loaded / evt.total;
                                    percentComplete = parseInt(percentComplete * 100);
                                    $('.myprogress').text(percentComplete + '%');
                                    $('.myprogress').css('width', percentComplete + '%');
                                }
                            }, false);
                            return xhr;
                        },
                        success: function (data) {
                         
                            $('#video_file_name').val(data);
                            $('.msg').text("File uploaded successfully!!");
                            $('#btn').removeAttr('disabled');
                        }
                    });
                });
            });
        </script>
<script type="text/javascript">
$(document).ready(function(e) {
           $("#news_type").change(function(){
          
           var type=$("#news_type").val();
              
              if(type=="video")
              { 
                //alert(type);
                $("#video_url_display").show();
                 $("#thumbnail").hide();
                 $("#gallery_image").hide();
                 $("#gallery_image_list").hide();
              } 
              else
              {   
                     
                $("#video_url_display").hide();               
                 $("#thumbnail").show();
                 $("#gallery_image").show();
                 $("#gallery_image_list").show();
              }    
              
         });
        });
</script>
  <script>
  $( function() {
    $( "#datepicker" ).datepicker();
  } );
  </script>

<div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="page_title_block">
            <div class="col-md-5 col-xs-12">
              <div class="page_title">Edit News</div>
            </div>
          </div>
          <div class="clearfix"></div>
          <div class="row mrg-top">
            <div class="col-md-12">
               
              <div class="col-md-12 col-sm-12">
                <?php if(isset($_SESSION['msg'])){?> 
                 <div class="alert alert-success alert-dismissible" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                  <?php echo $client_lang[$_SESSION['msg']] ; ?></a> </div>
                <?php unset($_SESSION['msg']);}?> 
              </div>
            </div>
          </div>
          <div class="card-body mrg_bottom"> 
            <form action="" name="edit_form" method="post" class="form form-horizontal" enctype="multipart/form-data">
                           
                            <input  type="hidden" name="news_id" value="<?php echo $_GET['news_id'];?>" />

              <div class="section">
                <div class="section-body">
                   
                   <div class="form-group">
                    <label class="col-md-3 control-label">Category :-</label>
                    <div class="col-md-6">
                      <select name="cat_id" id="cat_id" class="select2" required>
                        <option value="">--Select Category--</option>
                        <?php
                            while($cat_row=mysqli_fetch_array($cat_result))
                            {
                        ?>                       
                        <option value="<?php echo $cat_row['cid'];?>" <?php if($cat_row['cid']==$row['cat_id']){?>selected<?php }?>><?php echo $cat_row['category_name'];?></option>                           
                        <?php
                          }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label">News Type :-</label>
                    <div class="col-md-6">                       
                      <select name="news_type" id="news_type" style="width:280px; height:25px;" class="select2" required>
                             <option value="image" <?php if($row['news_type']=='image'){?>selected<?php }?>>Image</option>
                             <option value="video" <?php if($row['news_type']=='video'){?>selected<?php }?>>Video</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-md-3 control-label">News Title :-</label>
                    <div class="col-md-6">
                      <input type="text" name="news_title" id="news_title" value="<?php echo stripslashes($row['news_title']);?>" class="form-control" required>
                    </div>
                  </div>
                  
                  <div class="form-group">
                    <label class="col-md-3 control-label">News Description :-</label>
                    <div class="col-md-6">                    
                      <textarea name="news_description" id="news_description" class="form-control"><?php echo stripslashes($row['news_description']);?></textarea>

                      <script>
                      var roxyFileman = '<?php echo $ck_file_path;?>/fileman/index.html?integration=ckeditor';
                      $(function(){
                        CKEDITOR.replace( 'news_description',{filebrowserBrowseUrl:roxyFileman, 
                                                     filebrowserImageBrowseUrl:roxyFileman+'&type=image',
                                                     removeDialogTabs: 'link:upload;image:upload'});
                      });
                      </script>
                      <!--<script>CKEDITOR.replace( 'news_description' );</script>-->
                    </div>
                  </div><br>

                  <div id="video_url_display" class="form-group" <?php if($row['news_type']=='image'){?>style="display:none;"<?php }?>>
                    <label class="col-md-3 control-label">Video URL :-</label>
                    <div class="col-md-6">
                      <input type="text" name="video_url" id="video_url" value="<?php echo $row['video_url']?>" class="form-control">
                    </div>
                  </div>
                  

                  <div id="thumbnail" class="form-group" <?php if($row['news_type']=='video'){?>style="display:none;"<?php }?>>
                    <label class="col-md-3 control-label">News Image:-
                      <p class="control-label-help">(Recommended resolution: 200*400,250*500,300*600 or Rectangle)</p>
                    </label>
                    <div class="col-md-6">
                      <div class="fileupload_block">
                        <input type="file" name="news_image" value="" id="fileupload">
                         
                            <input type="hidden" name="news_thumbnail_name" id="news_thumbnail_name" value="<?php echo $row['news_image'];?>" class="form-control">
 
                       <div class="fileupload_img"><img type="image" src="assets/images/add-image.png" alt="category image" /></div>
                       
                      </div>
                    </div>
                       
                   
                  </div>
                
                  <div class="form-group" <?php if($row['news_type']=='video'){?>style="display:none;"<?php }?>>
                    <label class="col-md-3 control-label">&nbsp; </label>
                    <div class="col-md-6">
                      <?php if($row['news_image']!="") {?>
                            <div class="block_wallpaper"><img src="images/<?php echo $row['news_image'];?>" alt="category image" /></div>
                          <?php } ?>
                    </div>
                  </div><br> 
                       <div class="form-group" id="gallery_image" <?php if($row['news_type']=='video'){?>style="display:none;"<?php }?>>
                    <label class="col-md-3 control-label">Gallery Image :-
                      <p class="control-label-help">(Recommended resolution: 400*400,500*500 or Square)</p>
                    </label>
                    <div class="col-md-6">
                      <div class="fileupload_block">
                        <input type="file" name="news_gallery_image[]" value="" id="fileupload" multiple>
                            
                            <div class="fileupload_img"><img type="image" src="assets/images/add-image.png" alt="Featured image" /></div>
                           
                      </div>
                    </div>
                      
                  </div>
                  <div class="form-group" id="gallery_image_list" <?php if($row['news_type']=='video'){?>style="display:none;"<?php }?>>
                  <label class="col-md-3 control-label">&nbsp;</label>
                      <div class="row">
                          <?php
                            while ($row_img=mysqli_fetch_array($result1)) {?>
                               <div class="col-md-1 col-sm-6">
                          
                            <img src="images/<?php echo $row_img['news_gallery_image'];?>" class="img-responsive">
                            <a href="edit_news.php?image_id=<?php echo $row_img['id'];?>&news_id=<?php echo $_GET['news_id'];?>">Delete</a>
                           
                        </div>
                            <?php
                          }
                          ?>
                         
       
                     </div>
                  </div> 

                  <div class="form-group">
                    <label class="col-md-3 control-label">News Date :-</label>
                    <div class="col-md-6">
                      <input type="text" name="news_date" id="datepicker" value="<?php echo date('m/d/Y',$row['news_date']);?>" class="form-control">
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="col-md-9 col-md-offset-3">
                      <button type="submit" name="submit" class="btn btn-primary">Save</button>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
        
<?php include("includes/footer.php");?>       
