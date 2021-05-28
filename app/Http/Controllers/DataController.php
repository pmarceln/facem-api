<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Entities\Filter;
use App\Entities\Project;
use App\Entities\Photo;

class DataController extends Controller
{

    public function getAllData(Request $request)
    {
        $filters = Filter::orderBy('order')->get();
        $projects = Project::with('photos')->orderBy('order')->get();

        return response()->json(['filters' => $filters, 'projects' => $projects], Response::HTTP_OK);
    }

    public function addEditFilter(Request $request) {
        $fil = $request->filter;
        if ($fil['id']) {
            $filter = Filter::find($fil['id']);
        } else {
            $filter = new Filter();
        }
        $filter->is_active = $fil['is_active'];
        $filter->name = $fil['name'];
        $filter->order = $fil['order'];
        $filter->save();
        return response()->json($filter, Response::HTTP_OK);
    }

    public function updateFilterIsActive(Request $request)
    {
        $fil = $request->filter;
        $filter = Filter::find($fil['id']);
        $filter->is_active = $fil['is_active'];
        $filter->save();
        return response()->json($filter, Response::HTTP_OK);
    }

    public function updateProjectIsActive(Request $request)
    {
        $prj = $request->project;
        $project = Project::find($prj['id']);
        $project->is_active = $prj['is_active'];
        $project->save();
        return response()->json(["isSuccess"=>true], Response::HTTP_OK);
    }

    public function deleteFilter(Request $request) {
        $id = $request->id;
        $filter = Filter::find($id);

        if ($filter) {
            $filter->delete();
            return response()->json(null, Response::HTTP_OK);
        }

        return response()->json(null, Response::HTTP_NOT_FOUND);
    }

    public function addProject(Request $request) {
        $prj = $request->project;
        $project = new Project();
        $project->name = $prj['name'];
        $project->filters = $prj['filters'];

        if (isset($prj['icon_active']) && isset($prj['icon_hover'])) {
            $imageAct = $prj['icon_active'];
            $imageInfo = explode(";base64,", $imageAct);
            $imgExt = str_replace('data:image/', '', $imageInfo[0]);
            $imageAct = str_replace(' ', '+', $imageInfo[1]);
            $imageActive = uniqid().".".$imgExt;
            $project->icon = $imageActive;

            $imageHov = $prj['icon_hover'];
            $imageInfo = explode(";base64,", $imageHov);
            $imgExt = str_replace('data:image/', '', $imageInfo[0]);
            $image = str_replace(' ', '+', $imageInfo[1]);
            $imageHover = uniqid().".".$imgExt;
            $project->icon_hover = $imageHover;
        }
        if ($project->save()) {
            if (isset($prj['icon_active'])) {
                Storage::disk('uploads')->put($imageActive, base64_decode($imageAct));
            }
            if (isset($prj['icon_hover'])) {
                Storage::disk('uploads')->put($imageHover, base64_decode($imageHov));
            }
            return response()->json($project, Response::HTTP_OK);
        }

        return response()->json(["message"=>"Project cannot be saved!"], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function editProject(Request $request) {
        $prj = $request->project;
        $projectImages = $prj["prjImg"];
        $coverToDelete = [];

        $project = Project::find($prj["id"]);
        if ($project) {
            if (isset($prj['icon_active'])) {
                $imageAct = $prj['icon_active'];
                $imageInfo = explode(";base64,", $imageAct);
                $imgExt = str_replace('data:image/', '', $imageInfo[0]);
                $imageAct = str_replace(' ', '+', $imageInfo[1]);
                $imageActive = uniqid().".".$imgExt;
                $coverToDelete[] = $project->icon;
                $project->icon = $imageActive;
            }
            if (isset($prj['icon_hover'])) {
                $imageHov = $prj['icon_hover'];
                $imageInfo = explode(";base64,", $imageHov);
                $imgExt = str_replace('data:image/', '', $imageInfo[0]);
                $image = str_replace(' ', '+', $imageInfo[1]);
                $imageHover = uniqid().".".$imgExt;
                $coverToDelete[] = $project->icon_hover;
                $project->icon_hover = $imageHover;
            }

            $project->filters = $prj['filters'];
            $project->name = $prj["name"];
            if ($project->save()) {
                if (isset($prj['icon_active'])) {
                    Storage::disk('uploads')->put($imageActive, base64_decode($imageAct));
                }
                if (isset($prj['icon_hover'])) {
                    Storage::disk('uploads')->put($imageHover, base64_decode($imageHov));
                }
            }

            if (count($coverToDelete) > 0) {
                Storage::delete($coverToDelete);
            }
        }

        try {
            for ($i=0; $i < count($projectImages); $i++) {
                $imgPhotot = $projectImages[$i];
                $photo = new Photo();
                $photo->idproject = $prj["id"];
                $photo->order = $imgPhotot['order'];
                $photo->description = $imgPhotot['text'];

                $imageAct = $imgPhotot['image'];
                $imageInfo = explode(";base64,", $imageAct);
                $imgExt = str_replace('data:image/', '', $imageInfo[0]);
                $imageAct = str_replace(' ', '+', $imageInfo[1]);
                $imageActive = uniqid().".".$imgExt;
                $photo->photo = $imageActive;
                if ($photo->save()) {
                    Storage::disk('uploads')->put($imageActive, base64_decode($imageAct));
                }
            }

            return response()->json( ["success"=>true], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(["message"=>$e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function updateProjectPhotosDescriptionAndOrder(Request $request) {
        $prjPtsDescOrd = $request->projectPhotosDescriptionAndOrder;
        try {
            for ($i=0; $i < count($prjPtsDescOrd); $i++) {
                $photo = Photo::find($prjPtsDescOrd[$i]['id']);
                if ($photo) {
                    $photo->description = $prjPtsDescOrd[$i]['description'];
                    $photo->order = $prjPtsDescOrd[$i]['order'];
                    $photo->save();
                }
            }

            return response()->json( ["success"=>true], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(["message"=>$e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
