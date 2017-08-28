<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>WebUntis API</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
    <style>
      body {
        margin: 20px 0px;
      }
    </style>
  </head>
  
  <body>
    <div class="container">
      <h1>WebUntis API</h1>
      <div class="row">
        <div class="col-lg-6">
          <form>
            <div class="form-group">
              <label for="yearInput">Year</label>
              <select class="form-control" id="yearInput">
                <option value="7">2017/2018</option>
              </select>
            </div>
            <div class="form-group">
              <label for="departmentInput">Department</label>
              <select class="form-control" id="departmentInput">
                <option value="7">Muziek en Technologie</option>
              </select>
            </div>
            <div class="form-group">
              <label for="classInput">Classes</label>
              <select multiple class="form-control" id="classInput">
                <option value="7">MT jaar2 Alg</option>
                <option value="7">MT jaar2 Comp</option>
                <option value="7">MT jaar2 CSD</option>
              </select>
              <small class="form-text text-muted">Use CTRL-click or SHIFT-click to select multiple classes at once</small>
            </div>
            <div class="form-group">
              <label>Period</label>
              <div class="form-row">
                <div class="col">
                  <input type="date" class="form-control" id="startDateInput">
                </div>
                <div class="col">
                  <input type="date" class="form-control" id="endDateInput">
                </div>
              </div>
            </div>
            <div class="form-check">
              <label class="form-check-label">
              <input type="checkbox" class="form-check-input">
                Include holidays in the export
              </label>
            </div>
            <button type="submit" class="btn btn-primary">Show timetable</button>
            <button type="button" class="btn btn-secondary">Export to iCalendar</button>
          </form>
        </div>
        
        <div class="col-lg-6">
          <h3 class="text-muted">Monday 04-09-2017</h3>
          
          <div class="list-group">
            <div class="list-group-item">
              <h4>CSD: Theorie</h4>
              <i class="fa fa-fw fa-clock-o"></i> 18:15 - 19:45<br>
              <i class="fa fa-fw fa-map-marker"></i> IB152<br>
              <i class="fa fa-fw fa-users"></i> MT jaar 2, MT jaar2 CSD
            </div>

            <div class="list-group-item">
              <h4>CSD: Wiskunde, ST/LA: Wiskunde</h4>
              <i class="fa fa-fw fa-clock-o"></i> 19:45 - 21:15<br>
              <i class="fa fa-fw fa-map-marker"></i> IB219<br>
              <i class="fa fa-fw fa-users"></i> MT jaar 2, MT jaar2 CSD, MT jaar2 ST/LA
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"></script>
    <script src="https://use.fontawesome.com/8e5420d111.js"></script>
  </body>
</html>
